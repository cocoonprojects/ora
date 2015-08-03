<?php
namespace TaskManagement;

use IntegrationTest\Bootstrap;
use Zend\Http\Request;
use Zend\Http\Response;
use Zend\Mvc\MvcEvent;
use Zend\Mvc\Router\RouteMatch;
use Zend\Mvc\Router\Http\TreeRouteStack as HttpRouter;
use PHPUnit_Framework_TestCase;
use TaskManagement\Service\TaskService;
use TaskManagement\Controller\SharesController;
use ZFX\EventStore\Controller\Plugin\EventStoreTransactionPlugin;
use ZFX\Test\Authentication\OAuth2AdapterMock;

class LastSharesAssignmentProcessTest extends \PHPUnit_Framework_TestCase
{	
	protected $controller;
	protected $request;
	protected $response;
	protected $routeMatch;
	protected $event;
	
	protected $task;
	protected $owner;
	protected $member;
	protected $organization;
	
	protected $readModelTask;
	/**
	 * @var \DateInterval
	 */
	protected $intervalForCloseTasks;

	protected function setUp()
	{
		$serviceManager = Bootstrap::getServiceManager();
		$userService = $serviceManager->get('Application\UserService');
		$this->owner = $userService->findUser('60000000-0000-0000-0000-000000000000');
		$this->member = $userService->findUser('70000000-0000-0000-0000-000000000000');
		
		$streamService = $serviceManager->get('TaskManagement\StreamService');
		$stream = $streamService->getStream('00000000-1000-0000-0000-000000000000');
		
		$taskService = $serviceManager->get('TaskManagement\TaskService');
		$this->controller = new SharesController($taskService);
		$this->request	= new Request();
		$this->routeMatch = new RouteMatch(array('controller' => 'shares'));
		$this->event	  = new MvcEvent();
		$config = $serviceManager->get('Config');
		$routerConfig = isset($config['router']) ? $config['router'] : array();
		$router = HttpRouter::factory($routerConfig);

		$this->event->setRouter($router);
		$this->event->setRouteMatch($this->routeMatch);
		$this->controller->setEvent($this->event);
		$this->controller->setServiceLocator($serviceManager);

		$adapter = new OAuth2AdapterMock();
		$adapter->setEmail($this->owner->getEmail());
		$this->authService = $serviceManager->get('Zend\Authentication\AuthenticationService');
		$this->authService->authenticate($adapter);

		$pluginManager = $serviceManager->get('ControllerPluginManager');
		$this->controller->setPluginManager($pluginManager);

		$this->intervalForCloseTasks = new \DateInterval('P7D');
		
		$transactionManager = $serviceManager->get('prooph.event_store');
		$transactionManager->beginTransaction();
		try {
			$task = Task::create($stream, 'Cras placerat libero non tempor', $this->owner);
			$task->addMember($this->owner, Task::ROLE_OWNER);
			$task->addEstimation(1500, $this->owner);
			$task->addMember($this->member, Task::ROLE_MEMBER);
			$task->addEstimation(3100, $this->member);
			$task->complete($this->owner);
			$task->accept($this->owner, $this->intervalForCloseTasks);
			$task->assignShares([ $this->owner->getId() => 0.4, $this->member->getId() => 0.6 ], $this->member);
			$this->task = $taskService->addTask($task);
			$transactionManager->commit();
		} catch (\Exception $e) {
			var_dump($e);
			$transactionManager->rollback();
			throw $e;
		}
		$this->readModelTask = $taskService->findTask($this->task->getId());
	}
	
	public function testAssignSharesAsLast() {
		$this->routeMatch->setParam('id', $this->task->getId());
		 
		$this->request->setMethod('post');
		$params = $this->request->getPost();
		$params->set($this->owner->getId(), 50);
		$params->set($this->member->getId(), 50);
		
		$result   = $this->controller->dispatch($this->request);
		$response = $this->controller->getResponse();
		
		$this->assertEquals(201, $response->getStatusCode());
		$this->assertEquals(Task::STATUS_CLOSED, $this->task->getStatus());
		$this->assertEquals(Task::STATUS_CLOSED, $this->readModelTask->getStatus());
	}

	public function testSkipSharesAsLast() {
		$this->routeMatch->setParam('id', $this->task->getId());
		 
		$this->request->setMethod('post');
		
		$result   = $this->controller->dispatch($this->request);
		$response = $this->controller->getResponse();
		
		$this->assertEquals(201, $response->getStatusCode());
		$this->assertEquals(Task::STATUS_CLOSED, $this->task->getStatus());
		$this->assertEquals(Task::STATUS_CLOSED, $this->readModelTask->getStatus());
	}
}

<?php
namespace TaskManagement;

use IntegrationTest\Bootstrap;
use Zend\Http\Request;
use Zend\Http\Response;
use Zend\Mvc\MvcEvent;
use Zend\Mvc\Router\RouteMatch;
use Zend\Mvc\Router\Http\TreeRouteStack as HttpRouter;
use Zend\EventManager\EventManager;
use PHPUnit_Framework_TestCase;
use Rhumsaa\Uuid\Uuid;
use Application\Entity\User;
use TaskManagement\Task;
use TaskManagement\Service\TaskService;
use TaskManagement\Controller\SharesController;
use ZFX\EventStore\Controller\Plugin\EventStoreTransactionPlugin;

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

	protected function setUp()
	{
		$serviceManager = Bootstrap::getServiceManager();
		$userService = $serviceManager->get('Application\UserService');
		$this->owner = $userService->findUser('60000000-0000-0000-0000-000000000000');
		$this->member = $userService->findUser('70000000-0000-0000-0000-000000000000');
		
		$streamService = $serviceManager->get('TaskManagement\StreamService');
		$stream = $streamService->getStream('00000000-1000-0000-0000-000000000000');
		
		$taskServiceStub = $this->getMockBuilder(TaskService::class)
			->getMock();
		$this->controller = new SharesController($taskServiceStub);
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
		
		$transaction = $this->getMockBuilder(EventStoreTransactionPlugin::class)
			->disableOriginalConstructor()
			->setMethods(['begin', 'commit', 'rollback'])
			->getMock();
		$this->controller->getPluginManager()->setService('transaction', $transaction);

		$identity = $this->getMockBuilder('Zend\Mvc\Controller\Plugin\Identity')
			->disableOriginalConstructor()
			->getMock();
		$identity->method('__invoke')->willReturn(['user' => $this->owner]);
		$this->controller->getPluginManager()->setService('identity', $identity);
		
		$sharedManager = $serviceManager->get('SharedEventManager');
		$eventManager = new EventManager('TaskManagement\TaskService');
		$eventManager->setSharedManager($sharedManager);
		
		$this->task = Task::create($stream, 'Cras placerat libero non tempor', $this->owner);
		$this->task->setEventManager($eventManager);
		$this->task->addMember($this->owner, Task::ROLE_OWNER, 'ccde992b-5aa9-4447-98ae-c8115906dcb7');
		$this->task->addEstimation(1500, $this->owner);
		$this->task->addMember($this->member, Task::ROLE_MEMBER, 'cdde992b-5aa9-4447-98ae-c8115906dcb7');
		$this->task->addEstimation(3100, $this->member);
		$this->task->complete($this->owner);
		$this->task->accept($this->owner);
		$this->task->assignShares([ $this->owner->getId() => 0.4, $this->member->getId() => 0.6 ], $this->member);
		$this->controller->getTaskService()
			->method('getTask')
			->willReturn($this->task);
	}
	
	public function testAssignSharesAsLast() {
		$this->routeMatch->setParam('id', $this->task->getId()->toString());
		 
		$this->request->setMethod('post');
		$params = $this->request->getPost();
		$params->set($this->owner->getId(), 50);
		$params->set($this->member->getId(), 50);
		
		$result   = $this->controller->dispatch($this->request);
		$response = $this->controller->getResponse();
		
		$this->assertEquals(201, $response->getStatusCode());
		$this->assertEquals(Task::STATUS_CLOSED, $this->task->getStatus());
	}

	public function testSkipSharesAsLast() {
		$this->routeMatch->setParam('id', $this->task->getId()->toString());
		 
		$this->request->setMethod('post');
		
		$result   = $this->controller->dispatch($this->request);
		$response = $this->controller->getResponse();
		
		$this->assertEquals(201, $response->getStatusCode());
		$this->assertEquals(Task::STATUS_CLOSED, $this->task->getStatus());
	}
}
<?php
namespace TaskManagement;

use Guzzle\Http\Client;
use IntegrationTest\Bootstrap;
use PHPUnit_Framework_TestCase;
use Prooph\EventStore\EventStore;
use TaskManagement\Controller\SharesController;
use Zend\Http\Request;
use Zend\Http\Response;
use Zend\Mvc\MvcEvent;
use Zend\Mvc\Router\Http\TreeRouteStack as HttpRouter;
use Zend\Mvc\Router\RouteMatch;
use ZFX\Test\Authentication\AdapterMock;
use ZFX\Test\Authentication\OAuth2AdapterMock;

class MailNotificationProcessTest extends \PHPUnit_Framework_TestCase
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
	
	/**
	 * @var Client
	 */
	private $mailcatcher;
	/**
	 * @var EventStore
	 */
	private $transactionManager;
	/**
	 * @var \DateInterval
	 */
	protected $intervalForCloseTasks;
	
	protected function setUp()
	{
		$serviceManager = Bootstrap::getServiceManager();
		
		//Clean EmailMessages
		$this->mailcatcher = new Client('http://127.0.0.1:1080');
		$this->cleanEmailMessages();
		
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

		$adapter = new AdapterMock();
		$adapter->setEmail($this->owner->getEmail());
		$this->authService = $serviceManager->get('Zend\Authentication\AuthenticationService');
		$this->authService->authenticate($adapter);

		$pluginManager = $serviceManager->get('ControllerPluginManager');
		$this->controller->setPluginManager($pluginManager);

		$this->intervalForCloseTasks = new \DateInterval('P7D');
		
		$this->transactionManager = $serviceManager->get('prooph.event_store');
		$this->transactionManager->beginTransaction();
		$task = Task::create($stream, 'Cras placerat libero non tempor', $this->owner);
		$task->addMember($this->owner, Task::ROLE_OWNER);
		$task->addMember($this->member, Task::ROLE_MEMBER);
		$this->task = $taskService->addTask($task);
		$this->transactionManager->commit();
	}

	public function testEstimationAddedNotification() {
		//Clean Messages
		$this->cleanEmailMessages();
		$emails = $this->getEmailMessages();

		$this->transactionManager->beginTransaction();
		$this->task->addEstimation(1500, $this->owner);//Owner addEstimation (No-Mail)
		$this->task->addEstimation(3100, $this->member);//Member addEstimation (Mail)
		$this->transactionManager->commit();

		$emails = $this->getEmailMessages();

		$this->assertNotEmpty($emails);
		$this->assertEquals(1, count($emails));
		$this->assertEmailSubjectEquals('A member just estimated "', $emails[0]);
		$this->assertEmailHtmlContains('estimation', $emails[0]);
		$this->assertNotEmpty($emails[0]->recipients);
		$this->assertEquals($emails[0]->recipients[0], '<mark.rogers@ora.local>');
	}

	public function testSharesAssignedNotification(){
		$this->transactionManager->beginTransaction();
		$this->task->addEstimation(1500, $this->owner);
		$this->task->addEstimation(3100, $this->member);
		$this->task->complete($this->owner);
		$this->task->accept($this->owner, $this->intervalForCloseTasks);
		$this->transactionManager->commit();
		$this->cleanEmailMessages();

		$this->transactionManager->beginTransaction();
		$this->task->assignShares([ $this->owner->getId() => 0.4, $this->member->getId() => 0.6 ], $this->member);
		$this->transactionManager->commit();

		$email = $this->getLastEmailMessage();
		
		$this->assertNotNull($email);
		$this->assertEmailSubjectEquals('A member just assigned its shares to "', $email);
		$this->assertEmailHtmlContains('shares', $email);
		$this->assertNotEmpty($email->recipients);
		$this->assertEquals($email->recipients[0], '<mark.rogers@ora.local>');
		$this->cleanEmailMessages();
	}

	protected function cleanEmailMessages()
	{
		$request = $this->mailcatcher->delete('/messages');
		$response = $request->send();
	}
	
	protected function getEmailMessages()
	{
		$request = $this->mailcatcher->get('/messages');
		$response = $request->send();
		$json = json_decode($response->getBody());
		return $json;
	}
	
	public function getLastEmailMessage()
	{
		$messages = $this->getEmailMessages();
		if (empty($messages)) {
			$this->fail("No messages received");
		}
		// messages are in descending order
		return reset($messages);
	}
	public function assertEmailSubjectEquals($expected, $email, $description = '')
	{
		$this->assertStringStartsWith($expected, $email->subject, $description);
	}
	
	public function assertEmailHtmlContains($needle, $email, $description = '')
	{
		$request = $this->mailcatcher->get("/messages/{$email->id}.html");
		$response = $request->send();
		$this->assertContains($needle, (string)$response->getBody(), $description);
	}
}
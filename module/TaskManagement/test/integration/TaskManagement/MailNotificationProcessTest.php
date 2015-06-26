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
	 * @var \Guzzle\Http\Client
	 */
	private $mailcatcher;
	
	
	
	protected function setUp()
	{
		$serviceManager = Bootstrap::getServiceManager();
		
		//Clean EmailMessages
		$this->mailcatcher = new \Guzzle\Http\Client('http://127.0.0.1:1080');
		$this->cleanEmailMessages();
		
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
		//$this->task->addEstimation(1500, $this->owner);
		$this->task->addMember($this->member, Task::ROLE_MEMBER, 'cdde992b-5aa9-4447-98ae-c8115906dcb7');
		//$this->task->addEstimation(3100, $this->member);
		//$this->task->complete($this->owner);
		//$this->task->accept($this->owner);
		//$this->task->assignShares([ $this->owner->getId() => 0.4, $this->member->getId() => 0.6 ], $this->member);
		$this->controller->getTaskService()
		->method('getTask')
		->willReturn($this->task);
		
		
		
	}
	

	
	public function testEstimationAddedNotification() {
		//Clean Messages
		$this->cleanEmailMessages();
		
		$this->task->addEstimation(1500, $this->owner);//Owner addEstimation (No-Mail)
		$this->task->addEstimation(3100, $this->member);//Member addEstimation (Mail)
		
		$emails = $this->getEmailMessages();
		
		$this->assertNotEmpty($emails);
		$this->assertEquals(1, count($emails));
		$this->assertEmailSubjectEquals('A member just estimated "', $emails[0]);
		$this->assertEmailHtmlContains('estimation', $emails[0]);
		$this->assertNotEmpty($emails[0]->recipients);
		$this->assertEquals($emails[0]->recipients[0], '<mark.rogers@ora.local>');
	}
	
	
	
	public function testSharesAssignedNotification(){
		$this->task->addEstimation(1500, $this->owner);
		$this->task->addEstimation(3100, $this->member);
		$this->cleanEmailMessages();
		
		$this->task->complete($this->owner);
		$this->task->accept($this->owner);
		$this->task->assignShares([ $this->owner->getId() => 0.4, $this->member->getId() => 0.6 ], $this->member);
		
		$emails = $this->getEmailMessages();
		
		$this->assertNotEmpty($emails);
		$this->assertEquals(1, count($emails));
		$this->assertEmailSubjectEquals('A member just assigned its shares to "', $emails[0]);
		$this->assertEmailHtmlContains('shares', $emails[0]);
		$this->assertNotEmpty($emails[0]->recipients);
		$this->assertEquals($emails[0]->recipients[0], '<mark.rogers@ora.local>');
		$this->cleanEmailMessages();
	}
	
	
	
	protected function cleanEmailMessages()
	{
		$this->mailcatcher->delete('/messages')->send();
	}
	
	protected function getEmailMessages()
	{
		$jsonResponse = $this->mailcatcher->get('/messages')->send();
		return json_decode($jsonResponse->getBody());
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
		$response = $this->mailcatcher->get("/messages/{$email->id}.html")->send();
		$this->assertContains($needle, (string)$response->getBody(), $description);
	}
	
}
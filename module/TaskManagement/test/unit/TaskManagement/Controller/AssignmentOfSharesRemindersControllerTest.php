<?php
namespace TaskManagement\Controller;

use ZFX\Test\Controller\ControllerTest;
use Application\Entity\User;
use Application\Service\AclFactory;
use TaskManagement\Service\TaskService;
use TaskManagement\Service\NotifyMailListener;
use UnitTest\Bootstrap;
use ZFX\Acl\Controller\Plugin\IsAllowed;
use TaskManagement\Entity\Task;
use Zend\Mvc\Router\RouteMatch;

class AssignmentOfSharesRemindersControllerTest extends ControllerTest
{
	/**
	 * @var User
	 */
	private $systemUser;
	
	protected $readModelTask;
	protected $owner;
	protected $member;
	protected $taskServiceStub;

	public function setupMore()
	{
		$this->systemUser = User::create()->setRole(User::ROLE_SYSTEM);
	}
	
	protected function setupController()
	{
		//Task Service Mock
		$this->taskServiceStub = $this->getMockBuilder(TaskService::class)->getMock();	

		//$taskServiceStub = $this->getMockBuilder(TaskService::class)->getMock();
		$notifyMailListenerStub = $this->getMockBuilder(NotifyMailListener::class)->disableOriginalConstructor()->getMock();
		return new RemindersController($notifyMailListenerStub, $this->taskServiceStub);
	}

	protected function setupRouteMatch()
	{
		return ['controller' => 'reminders', 'id' => 'assignment-of-shares'];
	}
	
	public function testCreateAsAnonymous(){
		$this->setupAnonymous();
		
		$this->request->setMethod('post');
		$result = $this->controller->dispatch($this->request);
		$response = $this->controller->getResponse();
		$this->assertEquals(401, $response->getStatusCode());
	}
	
 	public function testCreateAsSystemUser(){
 		
 		$this->setupLoggedUser($this->systemUser);
 		
 		$this->request->setMethod('post');
 		$result = $this->controller->dispatch($this->request);
 		$response = $this->controller->getResponse();
 		$this->assertEquals(200, $response->getStatusCode());
 	}
 	
 	public function testCreateANonExistentReminder(){
 			
 		$this->setupLoggedUser($this->systemUser);
 		$this->routeMatch = new RouteMatch(['controller' => 'reminders', 'id' => 'random-reminder']);
 		$this->event->setRouteMatch($this->routeMatch);
 		$this->controller->setEvent($this->event);
 		
 		$this->request->setMethod('post');
 		$result = $this->controller->dispatch($this->request);
 		$response = $this->controller->getResponse();
 		$this->assertEquals(405, $response->getStatusCode());
 	}
 	
 	public function testCreateWithoutParam(){
 	
 		$this->setupLoggedUser($this->systemUser);
 		$this->routeMatch = new RouteMatch(['controller' => 'reminders']);
 		$this->event->setRouteMatch($this->routeMatch);
 		$this->controller->setEvent($this->event);
 	
 		$this->request->setMethod('post');
 		$result = $this->controller->dispatch($this->request);
 		$response = $this->controller->getResponse();
 		$this->assertEquals(405, $response->getStatusCode());
 	}
}
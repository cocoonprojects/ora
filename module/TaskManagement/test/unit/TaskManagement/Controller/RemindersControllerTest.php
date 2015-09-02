<?php
namespace TaskManagement\Controller;

use ZFX\Test\Controller\ControllerTest;
use Application\Entity\User;
use Application\Service\AclFactory;
use TaskManagement\Service\TaskService;
use TaskManagement\Service\NotifyMailListener;
use UnitTest\Bootstrap;
use ZFX\Acl\Controller\Plugin\IsAllowed;
use Zend\Mvc\Router\RouteMatch;

class RemindersControllerTest extends ControllerTest
{
	/**
	 * @var User
	 */
	private $systemUser;

	public function setupMore()
	{
		$this->systemUser = $this->getMockBuilder(User::class)->getMock();
		$this->systemUser->method('getRoleId')->willReturn(User::ROLE_SYSTEM);
	}
	
	protected function setupController()
	{
		$taskServiceStub = $this->getMockBuilder(TaskService::class)->getMock();
		$notifyMailListenerStub = $this->getMockBuilder(NotifyMailListener::class)->disableOriginalConstructor()->getMock();
		return new RemindersController($notifyMailListenerStub, $taskServiceStub);
	}
	
	protected function setupRouteMatch()
	{
		return [];
	}
	
	public function testCreateAsAnonymous(){
		$this->setupAnonymous();
		$this->routeMatch = new RouteMatch(['controller' => 'reminders', 'id' => 'assignment-of-shares']);
		$this->event->setRouteMatch($this->routeMatch);
		$this->controller->setEvent($this->event);
		
		$this->request->setMethod('post');
		
		$result = $this->controller->dispatch($this->request);
		$response = $this->controller->getResponse();
		$this->assertEquals(401, $response->getStatusCode());
	}
	
 	public function testCreateAsSystemUser(){
 		
 		$this->setupLoggedUser($this->systemUser);
 		$this->routeMatch = new RouteMatch(['controller' => 'reminders', 'id' => 'assignment-of-shares']);
 		$this->event->setRouteMatch($this->routeMatch);
 		$this->controller->setEvent($this->event);
 		
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
<?php
namespace TaskManagement\Controller;

use Application\Entity\User;
use People\Entity\Organization;
use TaskManagement\Entity\Stream;
use TaskManagement\Entity\Task;
use TaskManagement\Service\NotifyMailListener;
use TaskManagement\Service\TaskService;
use Zend\Mvc\Router\RouteMatch;
use ZFX\Test\Controller\ControllerTest;

class AssignmentOfSharesRemindersControllerTest extends ControllerTest
{
	/**
	 * @var User
	 */
	private $systemUser;
	
	protected $task;
	protected $owner;
	protected $member;
	protected $taskServiceStub;

	public function setupMore()
	{
		$this->systemUser = User::create()->setRole(User::ROLE_SYSTEM);
	}
	
	protected function setupController()
	{
		$this->task = new Task('1', new Stream('1', new Organization('1')));
		$this->owner = User::create()->setRole(User::ROLE_USER)->setEmail('taskowner@orateam.com');
		$this->member = User::create()->setRole(User::ROLE_USER)->setEmail('taskmember@orateam.com');
		
		$this->task->addMember($this->owner, Task::ROLE_OWNER, $this->owner, new \DateTime())
					->addMember($this->member, Task::ROLE_MEMBER, $this->member, new \DateTime())
					->setStatus(Task::STATUS_ACCEPTED);
		
		//Task Service Mock
		$this->taskServiceStub = $this->getMockBuilder(TaskService::class)->getMock();	
		$this->taskServiceStub->method ( 'findAcceptedTasksBefore' )->willReturn ( [$this->task] );
		
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
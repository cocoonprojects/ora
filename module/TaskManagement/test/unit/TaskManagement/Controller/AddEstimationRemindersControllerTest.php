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

class RemindersControllerTest extends ControllerTest
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
		$this->systemUser = $this->getMockBuilder(User::class)->getMock();
		$this->systemUser->method('getRoleId')->willReturn(User::ROLE_SYSTEM);
	}
	
	protected function setupController()
	{
		//Task Owner Mock
		$this->owner = $this->getMockBuilder ( User::class )->getMock ();
		$this->owner->method ( 'getId' )->willReturn ( '60000000-0000-0000-0000-000000000000' );
		$this->owner->method ( 'isMemberOf' )->willReturn ( true );
		$this->owner->method ( 'getRoleId' )->willReturn ( User::ROLE_USER );
		
		//Task Member Mock
		$this->member = $this->getMockBuilder ( User::class )->getMock ();
		$this->member->method ( 'getId' )->willReturn ( '70000000-0000-0000-0000-000000000000' );
		$this->member->method ( 'isMemberOf' )->willReturn ( true );
		$this->member->method('getEmail')->willReturn("task_member@oraproject.org");
		$this->member->method ( 'getRoleId' )->willReturn ( User::ROLE_USER );
		
		//ReadModelTask Mock
		$this->readModelTask = $this->getMockBuilder(Task::class)->disableOriginalConstructor()->getMock();
		$this->readModelTask->method('findMembersWithNoEstimation')->willReturn(array($this->member->getId()));
		$this->readModelTask->method('getId')->willReturn('taskID');
		$this->readModelTask->method('getMemberRole')->willReturn(Task::ROLE_OWNER);
		$this->readModelTask->method('getStatus')->willReturn(Task::STATUS_ONGOING);
		$this->readModelTask->method('getResourceId')->willReturn("Ora\Task");
		
		//User Service Mock
		$userServiceStub = $this->getMockBuilder(UserService::class)->getMock();
		$userServiceStub->method('findUser')->willReturn($this->member);
		
		//Task Service Mock
		$this->taskServiceStub = $this->getMockBuilder(TaskService::class)->getMock();	
		
		//$taskServiceStub = $this->getMockBuilder(TaskService::class)->getMock();
		$notifyMailListenerStub = $this->getMockBuilder(NotifyMailListener::class)->disableOriginalConstructor()->getMock();
		return new RemindersController($notifyMailListenerStub, $this->taskServiceStub);
	}
	
	protected function setupRouteMatch()
	{
		return ['controller' => 'reminders', 'id' => 'add-estimation'];
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
 	
 	public function testSendReminder() {
 		$_SERVER ['SERVER_NAME'] = 'oraproject.org';
 	
 		$this->setupLoggedUser ( $this->owner );
 	
 		$this->taskServiceStub->method ( 'findTask' )->willReturn ( $this->readModelTask );
 		$this->request->setMethod ( 'post' );
 		$params = $this->request->getPost ();
 		$params->set ( 'taskId', 'taskID' );
 	
 		$result = $this->controller->dispatch ( $this->request );
 		$response = $this->controller->getResponse ();
 	
 		$this->assertEquals ( 200, $response->getStatusCode () );
 		$this->assertEquals ( Task::STATUS_ONGOING, $this->readModelTask->getStatus () );
 	
 		unset ( $_SERVER ['SERVER_NAME'] );
 	}
 	
 	public function testSendReminderAsAnonymous() {
 		$this->setupAnonymous ();
 		$this->taskServiceStub->method ( 'findTask' )->willReturn ( $this->readModelTask );
 		$this->request->setMethod ( 'post' );
 		$params = $this->request->getPost ();
 		$params->set ( 'taskId', 'taskID' );
 	
 		$result = $this->controller->dispatch ( $this->request );
 		$response = $this->controller->getResponse ();
 	
 		$this->assertEquals ( 401, $response->getStatusCode () );
 	}
 	
 	public function testSendReminderNoTask() {
 		$this->setupLoggedUser ( $this->owner );
 	
 		$this->taskServiceStub->method ( 'findTask' )->willReturn ( null );
 		$this->request->setMethod ( 'post' );
 		$params = $this->request->getPost ();
 		$params->set ( 'taskId', 'Fake_Task_ID' );
 	
 		$result = $this->controller->dispatch ( $this->request );
 		$response = $this->controller->getResponse ();
 	
 		$this->assertEquals ( 404, $response->getStatusCode () );
 	}
}
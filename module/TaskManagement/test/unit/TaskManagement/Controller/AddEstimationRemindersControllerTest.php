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

class AddEstimationRemindersControllerTest extends ControllerTest
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
		$this->owner = User::create()->setRole(User::ROLE_USER);
		$this->member = User::create()->setRole(User::ROLE_USER);
		
		//ReadModelTask
		$this->readModelTask = new Task('0000000000');
		$this->readModelTask->addMember($this->owner, Task::ROLE_OWNER, $this->owner, new \DateTime())
							->addMember($this->member, Task::ROLE_MEMBER, $this->member, new \DateTime())
							->setStatus(Task::STATUS_ONGOING);
		
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
 	
 		$this->setupLoggedUser ( $this->owner );
 	
 		$this->taskServiceStub->method ( 'findTask' )->willReturn ( $this->readModelTask );
 		$this->request->setMethod ( 'post' );
 		$params = $this->request->getPost ();
 		$params->set ( 'taskId', 'taskID' );
 	
 		$result = $this->controller->dispatch ( $this->request );
 		$response = $this->controller->getResponse ();
 	
 		$this->assertEquals ( 200, $response->getStatusCode () );
 		$this->assertEquals ( Task::STATUS_ONGOING, $this->readModelTask->getStatus () );
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
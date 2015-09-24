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

class AddEstimationRemindersControllerTest extends ControllerTest
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
		$this->systemUser = $this->getMockBuilder(User::class)
			->disableOriginalConstructor()
			->getMock();
		$this->systemUser->method('getRoleId')->willReturn(User::ROLE_SYSTEM);
	}
	
	protected function setupController()
	{
		$this->owner = User::create()->setRole(User::ROLE_USER);
		$this->member = User::create()->setRole(User::ROLE_USER);

		$this->task = new Task('1', new Stream('1', new Organization('1')));
		$this->task->addMember($this->owner, Task::ROLE_OWNER, $this->owner, new \DateTime())
					->addMember($this->member, Task::ROLE_MEMBER, $this->member, new \DateTime())
					->setStatus(Task::STATUS_ONGOING);
		
		//Task Service Mock
		$this->taskServiceStub = $this->getMockBuilder(TaskService::class)->getMock();
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
 	
 		$this->taskServiceStub->method ( 'findTask' )->willReturn ( $this->task );
 		$this->request->setMethod ( 'post' );
 		$params = $this->request->getPost ();
 		$params->set ( 'taskId', 'taskID' );
 	
 		$result = $this->controller->dispatch ( $this->request );
 		$response = $this->controller->getResponse ();
 	
 		$this->assertEquals ( 200, $response->getStatusCode () );
 		$this->assertEquals ( Task::STATUS_ONGOING, $this->task->getStatus () );
 	}
 	
 	public function testSendReminderAsAnonymous() {
 		$this->setupAnonymous ();
 		$this->taskServiceStub->method ( 'findTask' )->willReturn ( $this->task );
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
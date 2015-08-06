<?php
namespace TaskManagement\Controller;

use UnitTest\Bootstrap;
use ZFX\Test\Controller\ControllerTest;
use TaskManagement\Service\TaskService;
use TaskManagement\Service\NotifyMailListener;
use Application\Entity\User;
use AcMailer\Service\MailService;
use Application\Service\UserService;
use TaskManagement\Entity\Task;



class MailControllerTest extends ControllerTest{
	
	protected $readModelTask;
	protected $owner;
	protected $member;
	protected $taskServiceStub;
	protected $mailListener;
	protected $authorizeServiceStub;
	
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
		
		$serviceManager = Bootstrap::getServiceManager();
		
		//ReadModelTask Mock
		$this->readModelTask = $this->getMockBuilder(Task::class)->disableOriginalConstructor()->getMock();
		$this->readModelTask->method('findMembersWithNoEstimation')->willReturn(array($this->member->getId()));
		$this->readModelTask->method('getId')->willReturn('taskID');
		$this->readModelTask->method('getMemberRole')->willReturn(Task::ROLE_OWNER);
		$this->readModelTask->method('getStatus')->willReturn(Task::STATUS_ONGOING);
		$this->readModelTask->method('getResourceId')->willReturn("Ora\Task");	
		
		//MailService
		$mailService = $serviceManager->get(MailService::class);

		//User Service Mock
		$userServiceStub = $this->getMockBuilder(UserService::class)->getMock();
		$userServiceStub->method('findUser')->willReturn($this->member);

		//Task Service Mock
		$this->taskServiceStub = $this->getMockBuilder(TaskService::class)->getMock();
		
		//MailListener
		$this->mailListener = new NotifyMailListener($mailService, $userServiceStub, $this->taskServiceStub);
		
		//ACL Mock
		$this->authorizeServiceStub = $this->getMockBuilder ( Acl::class )->disableOriginalConstructor ()->getMock ();
		
		return new MailController($this->mailListener, $this->taskServiceStub,$this->authorizeServiceStub);
	}
	
	protected function setupRouteMatch()
	{
		return ['controller' => 'mail'];
	}
	
	protected function setUp()
	{
		parent::setUp();
	}

	public function testSendReminder() {
		$_SERVER ['SERVER_NAME'] = 'oraproject.org';
		
		$this->setupLoggedUser ( $this->owner );
		
		$this->taskServiceStub->method ( 'findTask' )->willReturn ( $this->readModelTask );
		
		$this->routeMatch->setParam ( 'id', 'taskID' );
		
		$this->request->setMethod ( 'post' );
		$params = $this->request->getPost ();
		$params->set ( 'type', 'add-estimation' );
		
		$result = $this->controller->dispatch ( $this->request );
		$response = $this->controller->getResponse ();
		
		$this->assertEquals ( 200, $response->getStatusCode () );
		$this->assertEquals ( Task::STATUS_ONGOING, $this->readModelTask->getStatus () );
		
		unset ( $_SERVER ['SERVER_NAME'] );
	}
	public function testSendReminderAsAnonymous() {
		$this->setupAnonymous ();
		$this->taskServiceStub->method ( 'findTask' )->willReturn ( $this->readModelTask );
		
		$this->routeMatch->setParam ( 'id', 'taskID' );
		
		$this->request->setMethod ( 'post' );
		$params = $this->request->getPost ();
		$params->set ( 'type', 'add-estimation' );
		
		$result = $this->controller->dispatch ( $this->request );
		$response = $this->controller->getResponse ();
		
		$this->assertEquals ( 403, $response->getStatusCode () );
	}
	
	public function testSendReminderWithNoParams() {
		$this->setupLoggedUser ( $this->owner );
		
		$this->taskServiceStub->method ( 'findTask' )->willReturn ( $this->readModelTask );
		
		$this->routeMatch->setParam ( 'id', 'taskID' );
		
		$this->request->setMethod ( 'post' ); // Post with no params
		
		$result = $this->controller->dispatch ( $this->request );
		$response = $this->controller->getResponse ();
		
		$this->assertEquals ( 400, $response->getStatusCode () );
	}
	
	public function testSendReminderWithWrongParams() {
		$this->setupLoggedUser ( $this->owner );
		
		$this->taskServiceStub->method ( 'findTask' )->willReturn ( $this->readModelTask );
		
		$this->routeMatch->setParam ( 'id', 'taskID' );
		
		$this->request->setMethod ( 'post' );
		$params = $this->request->getPost ();
		$params->set ( 'type', 'wrong-parameter' );
		
		$result = $this->controller->dispatch ( $this->request );
		$response = $this->controller->getResponse ();
		
		$this->assertEquals ( 405, $response->getStatusCode () );
	}
	
	public function testSendReminderNoTask() {
		$this->setupLoggedUser ( $this->owner );
		
		$this->taskServiceStub->method ( 'findTask' )->willReturn ( null );
		
		$this->routeMatch->setParam ( 'id', 'Fake_Task_ID' );
		
		$this->request->setMethod ( 'post' );
		$params = $this->request->getPost ();
		$params->set ( 'type', 'add-estimation' );
		
		$result = $this->controller->dispatch ( $this->request );
		$response = $this->controller->getResponse ();
		
		$this->assertEquals ( 404, $response->getStatusCode () );
	}
	
}
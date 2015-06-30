<?php
namespace TaskManagement\Controller;

use ZFX\Test\Controller\ControllerTest;
use Zend\Permissions\Acl\Acl;
use Application\Entity\User;
use People\Organization;
use TaskManagement\Stream;
use TaskManagement\Task;
use TaskManagement\Service\TaskService;
use TaskManagement\Controller\TransitionsController;

class TransitionsControllerTest extends ControllerTest {
	
	private $user;
	
	private $sysUser;
	
	private $authorizeServiceStub;
	
	public function __construct()
	{
		$this->user = User::create();
		$this->user->setFirstname('John');
		$this->user->setLastname('Doe');
		
		$this->sysUser = User::createSystemUser();
	}
	
	protected function setupController()
	{
		$taskServiceStub = $this->getMockBuilder(TaskService::class)->getMock();
		$this->authorizeServiceStub = $this->getMockBuilder(Acl::class)
		->disableOriginalConstructor()
		->getMock();
		
		$controller = new TransitionsController($taskServiceStub, $this->authorizeServiceStub); 
		$controller->setIntervalForCloseTasks(new \DateInterval('P7D'));
		
		return $controller;
	}
	
	protected function setupRouteMatch()
	{
		return ['controller' => 'transitions'];
	}
	
	
	public function testtApplyTimeboxBlocked(){
	
		$this->controller->getAclService()->method('isAllowed')->willReturn(false);
			
		$this->setupLoggedUser($this->user);
		
		$this->request->setMethod('post');
		$params = $this->request->getPost();
		$params->set('action', 'close');
		
		$result = $this->controller->dispatch($this->request);	
		$response = $this->controller->getResponse();	
		$this->assertEquals(405, $response->getStatusCode());
	}
	
	public function testApplyTimeboxToCloseAnAcceptedTasks(){
	
		$this->setupLoggedUser($this->sysUser);
	
 		$this->controller->getAclService()->method('isAllowed')->willReturn(true);
		
		$taskToClose = $this->setupTask();
		$taskToClose->addMember($this->user, Task::ROLE_OWNER);
		$taskToClose->addEstimation(1, $this->user);
		$taskToClose->complete($this->user);
		$taskToClose->accept($this->user);
		
		$this->controller->getTaskService()
		->expects($this->once())
		->method('findAcceptedTasksBefore')
		->willReturn(array($taskToClose));
		 
		$this->controller->getTaskService()
		->expects($this->once())
		->method('getTask')
		->willReturn($taskToClose);
		 
		//dispatch
		$this->request->setMethod('post');
 		$params = $this->request->getPost();
 		$params->set('action', 'close');

 		$result = $this->controller->dispatch($this->request);
		$response = $this->controller->getResponse();
	
		//controllo che il task abbia lo stato corretto
		$this->assertEquals(200, $response->getStatusCode());
		$this->assertEquals(Task::STATUS_CLOSED, $taskToClose->getStatus());
		
	}
	
	
	protected function setupStream(){
	
		$organization = Organization::create('My brand new Orga', $this->getLoggedUser());
		return Stream::create($organization, 'Really useful stream', $this->getLoggedUser());
	}
	
	protected function setupTask(){
	
		$stream = $this->setupStream();
		return Task::create($stream, 'task subject', $this->getLoggedUser());
	}
}
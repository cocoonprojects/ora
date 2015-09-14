<?php
namespace TaskManagement\Controller;

use People\Entity\OrganizationMembership;
use ZFX\Test\Controller\ControllerTest;
use Zend\Permissions\Acl\Acl;
use Application\Entity\User;
use People\Organization;
use TaskManagement\Stream;
use TaskManagement\Task;
use TaskManagement\Service\TaskService;
use TaskManagement\Controller\TransitionsController;

class TransitionsControllerTest extends ControllerTest
{
	/**
	 * @var User
	 */
	private $systemUser;
	/**
	 * @var User
	 */
	private $user;
	/**
	 * @var Task
	 */
	private $task;

	public function setupMore()
	{
		$this->systemUser = $this->getMockBuilder(User::class)->getMock();
		$this->systemUser->method('getRoleId')->willReturn(User::ROLE_SYSTEM);
		
		$this->user = $this->getMockBuilder(User::class)->getMock();
		$this->user->method('isMemberOf')->willReturn(true);
		$this->user->method('getRoleId')->willReturn(User::ROLE_USER);		
		
		$organization = Organization::create('My brand new Orga', $this->user);
		$stream = Stream::create($organization, 'Really useful stream', $this->user);
		$this->task = Task::create($stream, 'task subject', $this->user);
		$this->task->addMember($this->user, Task::ROLE_OWNER);
		$this->task->addEstimation(1, $this->user);
		$this->task->complete($this->user);
		$this->task->accept($this->user, $this->controller->getIntervalForCloseTasks());
	}
	
	protected function setupController()
	{
		$taskServiceStub = $this->getMockBuilder(TaskService::class)->getMock();
		$controller = new TransitionsController($taskServiceStub); 
		
		return $controller;
	}
	
	protected function setupRouteMatch()
	{
		return ['controller' => 'transitions'];
	}

	public function testCreateAsAnonymous()
	{
		$this->setupAnonymous();
		
		$this->request->setMethod('post');
		$params = $this->request->getPost();
		$params->set('action', 'close');
		
		$result = $this->controller->dispatch($this->request);
		$response = $this->controller->getResponse();
		$this->assertEquals(401, $response->getStatusCode());
	}
	
	public function testCreate(){
	
		$this->setupLoggedUser($this->systemUser);
		
		$this->controller->getTaskService()
			->expects($this->once())
			->method('findAcceptedTasksBefore')
			->willReturn(array($this->task));
		 
		$this->controller->getTaskService()
			->expects($this->once())
			->method('getTask')
			->willReturn($this->task);
		 
		//dispatch
		$this->request->setMethod('post');
 		$params = $this->request->getPost();
 		$params->set('action', 'close');

 		$result = $this->controller->dispatch($this->request);
		$response = $this->controller->getResponse();
	
		//controllo che il task abbia lo stato corretto
		$this->assertEquals(200, $response->getStatusCode());
		$this->assertEquals(Task::STATUS_CLOSED, $this->task->getStatus());
		
	}
}

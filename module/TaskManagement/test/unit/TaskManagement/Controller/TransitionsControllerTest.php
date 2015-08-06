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
	private $adminUser;
	/**
	 * @var Task
	 */
	private $task;

	public function setupMore()
	{
		$this->adminUser = $this->getMockBuilder(User::class)->getMock();
		$this->adminUser->method('getRoleId')->willReturn(User::ROLE_ADMIN);
		$this->adminUser->method('isMemberOf')->willReturn(true);

		$organization = Organization::create('My brand new Orga', $this->adminUser);
		$stream = Stream::create($organization, 'Really useful stream', $this->adminUser);
		$this->task = Task::create($stream, 'task subject', $this->adminUser);
		$this->task->addMember($this->adminUser, Task::ROLE_OWNER);
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
	
		$this->setupLoggedUser($this->adminUser);
		
		$this->task->addEstimation(1, $this->adminUser);
		$this->task->complete($this->adminUser);
		$this->task->accept($this->adminUser, $this->controller->getIntervalForCloseTasks());
		
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
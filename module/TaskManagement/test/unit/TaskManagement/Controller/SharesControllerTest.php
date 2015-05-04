<?php
namespace TaskManagement\Controller;

use ZFX\Test\Controller\ControllerTest;
use Rhumsaa\Uuid\Uuid;
use Application\Entity\User;
use TaskManagement\Service\TaskService;
use TaskManagement\Task;
use TaskManagement\Stream;

class SharesControllerTest extends ControllerTest
{
	protected $task;
	protected $owner;
	protected $member;
	protected $organization;

	protected function setupController()
	{
		$taskServiceStub = $this->getMockBuilder(TaskService::class)->getMock();
		return new SharesController($taskServiceStub);
	}
	
	protected function setupRouteMatch()
	{
		return ['controller' => 'shares'];
	}
	
	protected function setUp()
	{
		parent::setUp();
		$this->owner = $this->getMockBuilder(User::class)
			->getMock();
		$this->owner->method('getId')
			->willReturn('60000000-0000-0000-0000-000000000000');
		
		$this->member = $this->getMockBuilder(User::class)
			->getMock();
		$this->member->method('getId')
			->willReturn('70000000-0000-0000-0000-000000000000');
		
		$this->setupLoggedUser($this->owner);

		$stream = $this->getMockBuilder(Stream::class)
			->disableOriginalConstructor()
			->getMock();
		$stream->method('getId')
			->willReturn(Uuid::fromString('00000000-1000-0000-0000-000000000000'));
		
		$this->task = Task::create($stream, 'Cras placerat libero non tempor', $this->owner);
		$this->task->addMember($this->owner, Task::ROLE_OWNER, 'ccde992b-5aa9-4447-98ae-c8115906dcb7');
		$this->task->addMember($this->member, Task::ROLE_MEMBER, 'cdde992b-5aa9-4447-98ae-c8115906dcb7');
		
		$this->task->addEstimation(1500, $this->owner);
		$this->task->addEstimation(3100, $this->member);
		
		$this->task->complete($this->owner);
	}
	
	public function testAssignSharesAsAnonymous()
	{
		$identity = $this->getMockBuilder('Zend\Mvc\Controller\Plugin\Identity')
			->disableOriginalConstructor()
			->getMock();
		$identity->method('__invoke')->willReturn(null);
		$this->controller->getPluginManager()->setService('identity', $identity);
		
		$this->task->accept($this->owner);
		$service = $this->controller->getTaskService();
		$service->method('getTask')
				->willReturn($this->task);
		
		$this->routeMatch->setParam('id', $this->task->getId()->toString());
		
		$this->request->setMethod('post');
		$params = $this->request->getPost();
		$params->set($this->owner->getId(), 40);
		$params->set($this->member->getId(), 60);
		 
		$result   = $this->controller->dispatch($this->request);
		$response = $this->controller->getResponse();
	
		$this->assertEquals(401, $response->getStatusCode());
	}
	
	public function testAssignShares()
	{
		$this->task->accept($this->owner);
		$service = $this->controller->getTaskService();
		$service->method('getTask')
				->willReturn($this->task);
		
		$this->routeMatch->setParam('id', $this->task->getId()->toString());
		
		$this->request->setMethod('post');
		$params = $this->request->getPost();
		$params->set($this->owner->getId(), 40);
		$params->set($this->member->getId(), 60);
		 
		$result   = $this->controller->dispatch($this->request);
		$response = $this->controller->getResponse();
	
		$this->assertEquals(201, $response->getStatusCode());
	}
	
	public function testSkipAssignShares()
	{
		$this->task->accept($this->owner);
		$service = $this->controller->getTaskService();
		$service->method('getTask')
				->willReturn($this->task);
		
		$this->routeMatch->setParam('id', $this->task->getId()->toString());
		
		$this->request->setMethod('post');
		 
		$result   = $this->controller->dispatch($this->request);
		$response = $this->controller->getResponse();
	
		$this->assertEquals(201, $response->getStatusCode());
	}
	
	public function testAssignSharesWithMoreThan100Share()
	{
		$this->task->accept($this->owner);
		$service = $this->controller->getTaskService();
		$service->method('getTask')
				->willReturn($this->task);
		
		$this->routeMatch->setParam('id', $this->task->getId()->toString());
		
		$this->request->setMethod('post');
		$params = $this->request->getPost();
		$params->set($this->owner->getId(), 101);
		$params->set($this->member->getId(), 60);
		 
		$result   = $this->controller->dispatch($this->request);
		$response = $this->controller->getResponse();
	
		$this->assertEquals(400, $response->getStatusCode());
	}
	
	public function testAssignSharesWithLessThan0Share()
	{
		$this->task->accept($this->owner);
		$service = $this->controller->getTaskService();
		$service->method('getTask')
				->willReturn($this->task);
		
		$this->routeMatch->setParam('id', $this->task->getId()->toString());
		
		$this->request->setMethod('post');
		$params = $this->request->getPost();
		$params->set($this->owner->getId(), 40);
		$params->set($this->member->getId(), -1);
		 
		$result   = $this->controller->dispatch($this->request);
		$response = $this->controller->getResponse();
	
		$this->assertEquals(400, $response->getStatusCode());
	}
	
	public function testAssignSharesWithMoreThan100TotalShares()
	{
		$this->task->accept($this->owner);
		$service = $this->controller->getTaskService();
		$service->method('getTask')
				->willReturn($this->task);
		
		$this->routeMatch->setParam('id', $this->task->getId()->toString());
		
		$this->request->setMethod('post');
		$params = $this->request->getPost();
		$params->set($this->owner->getId(), 40);
		$params->set($this->member->getId(), 70);
		 
		$result   = $this->controller->dispatch($this->request);
		$response = $this->controller->getResponse();
	
		$this->assertEquals(400, $response->getStatusCode());
	}
	
	public function testAssignSharesWithUnexistingTask()
	{
		$service = $this->controller->getTaskService();
		$service->method('getTask')
				->willReturn(null);
		
		$this->routeMatch->setParam('id', $this->task->getId()->toString());
		
		$this->request->setMethod('post');
		$params = $this->request->getPost();
		$params->set($this->owner->getId(), 40);
		$params->set($this->member->getId(), 60);
		 
		$result   = $this->controller->dispatch($this->request);
		$response = $this->controller->getResponse();
	
		$this->assertEquals(404, $response->getStatusCode());
	}
	
	public function testAssignSharesWithMissingMember()
	{
		$this->task->accept($this->owner);
		$service = $this->controller->getTaskService();
		$service->method('getTask')
				->willReturn($this->task);
		
		$this->routeMatch->setParam('id', $this->task->getId()->toString());
		
		$this->request->setMethod('post');
		$params = $this->request->getPost();
		$params->set($this->owner->getId(), 100);
		 
		$result   = $this->controller->dispatch($this->request);
		$response = $this->controller->getResponse();
	
		$this->assertEquals(400, $response->getStatusCode());
	}
	
	public function testAssignSharesByNonMember()
	{
		$this->task->accept($this->owner);
		$identity = $this->getMockBuilder('Zend\Mvc\Controller\Plugin\Identity')
			->disableOriginalConstructor()
			->getMock();
		$identity->method('__invoke')->willReturn(['user' => User::create()]);
		$this->controller->getPluginManager()->setService('identity', $identity);
		 
		$service = $this->controller->getTaskService();
		$service->method('getTask')
			->willReturn($this->task);
		 
		$this->routeMatch->setParam('id', $this->task->getId()->toString());
		 
		$this->request->setMethod('post');
		$params = $this->request->getPost();
		$params->set($this->owner->getId(), 40);
		$params->set($this->member->getId(), 60);
		
		$result   = $this->controller->dispatch($this->request);
		$response = $this->controller->getResponse();
		
		$this->assertEquals(403, $response->getStatusCode());
	}
	
	public function testAssignSharesToACompletedTask()
	{
		$service = $this->controller->getTaskService();
		$service->method('getTask')
		->willReturn($this->task);
		
		$this->routeMatch->setParam('id', $this->task->getId()->toString());
		
		$this->request->setMethod('post');
		$params = $this->request->getPost();
		$params->set($this->owner->getId(), 40);
		$params->set($this->member->getId(), 60);
		 
		$result   = $this->controller->dispatch($this->request);
		$response = $this->controller->getResponse();
		 
		$this->assertEquals(412, $response->getStatusCode());
	}
	
	public function testAssignSharesToANonMembers()
	{
		$this->task->accept($this->owner);
		$service = $this->controller->getTaskService();
		$service->method('getTask')
			->willReturn($this->task);
		 
		$this->routeMatch->setParam('id', $this->task->getId()->toString());
		 
		$this->request->setMethod('post');
		$params = $this->request->getPost();
		$params->set(User::create()->getId(), 20);
		$params->set($this->owner->getId(), 20);
		$params->set($this->member->getId(), 60);
		
		$result   = $this->controller->dispatch($this->request);
		$response = $this->controller->getResponse();
		
		$this->assertEquals(400, $response->getStatusCode());
	}

	public function testAssignSharesToANonMembersExtraShares()
	{
		$this->task->accept($this->owner);
		$service = $this->controller->getTaskService();
		$service->method('getTask')
			->willReturn($this->task);
		 
		$this->routeMatch->setParam('id', $this->task->getId()->toString());
		 
		$this->request->setMethod('post');
		$params = $this->request->getPost();
		$params->set(User::create()->getId(), 20);
		$params->set($this->owner->getId(), 40);
		$params->set($this->member->getId(), 60);
		
		$result   = $this->controller->dispatch($this->request);
		$response = $this->controller->getResponse();
		
		$this->assertEquals(201, $response->getStatusCode());
	}
}
<?php
namespace TaskManagement\Controller;

use Application\Entity\User;
use People\Entity\Organization;
use People\Service\OrganizationService;
use TaskManagement\Entity\Stream;
use TaskManagement\Entity\Task;
use TaskManagement\Service\StreamService;
use TaskManagement\Service\TaskService;
use ZFX\Test\Controller\ControllerTest;

/**
 * Class TasksControllerTest
 * @package TaskManagement\Controller
 */
class TasksControllerTest extends ControllerTest {
	
	private $user;

	private $stream;

	private $organization;

	protected function setupController()
	{
		$taskServiceStub = $this->getMockBuilder(TaskService::class)->getMock();
		$streamServiceStub = $this->getMockBuilder(StreamService::class)->getMock();
		$organizationServiceStub = $this->getMockBuilder(OrganizationService::class)->getMock();
		return new TasksController($taskServiceStub, $streamServiceStub, $organizationServiceStub);
	}

	protected function setupRouteMatch()
	{
		return ['controller' => 'tasks'];
	}

	protected function setupMore() {
		$this->user = User::create();
		$this->user->setFirstname('John');
		$this->user->setLastname('Doe');
		$this->user->setRole(User::ROLE_USER);
		$this->organization = new Organization('00000');
		$this->stream = new Stream('00000', $this->organization);
	}
	
	public function testGetEmptyListFromAStream()
	{
		$this->user->addMembership($this->organization);
		$this->setupLoggedUser($this->user);

		$this->controller->getOrganizationService()
			->expects($this->once())
			->method('findOrganization')
			->with($this->organization->getId())
			->willReturn($this->organization);

		$this->controller->getTaskService()
			->expects($this->once())
			->method('findTasks')
			->willReturn([]);

		$this->controller->getTaskService()
			->expects($this->once())
			->method('countOrganizationTasks')
			->with($this->organization)
			->willReturn(0);
		
		$this->request->setMethod('get');
		$params = $this->request->getQuery();
		$params->set('streamId', $this->stream->getId());

		$this->routeMatch->setParam('orgId', $this->organization->getId());

		$result   = $this->controller->dispatch($this->request);
		$response = $this->controller->getResponse();
		
		$this->assertEquals(200, $response->getStatusCode());
		$arrayResult = json_decode($result->serialize(), true);
		$this->assertCount(0, $arrayResult['_embedded']['ora:task']);
		$this->assertNotEmpty($arrayResult['_links']['self']['href']);
		$this->assertArrayNotHasKey('next', $arrayResult['_links']);
		$this->assertEquals(0, $arrayResult['count']);
		$this->assertEquals(0, $arrayResult['total']);
	}

	public function testGetListFromPopulatedStream()
	{
		$this->user->addMembership($this->organization);
		$this->setupLoggedUser($this->user);

		$this->controller->getOrganizationService()
			->expects($this->once())
			->method('findOrganization')
			->with($this->organization->getId())
			->willReturn($this->organization);
		
		$this->controller->getTaskService()
			->expects($this->once())
			->method('countOrganizationTasks')
			->with($this->organization)
			->willReturn(1);

		$task = new Task('00001', $this->stream);

		$this->controller->getTaskService()
			->expects($this->once())
			->method('findTasks')
			->willReturn([$task]);

		$this->request->setMethod('get');
		$params = $this->request->getQuery();
		$params->set('streamId', $this->stream->getId());

		$this->routeMatch->setParam('orgId', $this->organization->getId());

		$result   = $this->controller->dispatch($this->request);
		$response = $this->controller->getResponse();
	
		$this->assertEquals(200, $response->getStatusCode());
		$arrayResult = json_decode($result->serialize(), true);
		$this->assertCount(1, $arrayResult['_embedded']['ora:task']);
		$this->assertNotEmpty($arrayResult['_links']['self']['href']);
		$this->assertArrayNotHasKey('next', $arrayResult['_links']);
		$this->assertEquals(1, $arrayResult['count']);
		$this->assertEquals(1, $arrayResult['total']);
	}
	
	public function testGetListAsAnonymous()
	{
		$this->setupAnonymous();
		$this->request->setMethod('get');
		
		$this->controller->getOrganizationService()
			->expects($this->once())
			->method('findOrganization')
			->with($this->organization->getId())
			->willReturn($this->organization);
		
		$this->routeMatch->setParam('orgId', $this->organization->getId());
	
		$result   = $this->controller->dispatch($this->request);
		$response = $this->controller->getResponse();
	
		$this->assertEquals(401, $response->getStatusCode());
	}

	public function testGetListWithoutOrganizationId()
	{
		$this->user->addMembership($this->organization);
		$this->setupLoggedUser($this->user);

		$result   = $this->controller->dispatch($this->request);
		$response = $this->controller->getResponse();

		$this->assertEquals(400, $response->getStatusCode());
	}

	public function testGetListWithNotExistingOrganizationId()
	{
		$this->user->addMembership($this->organization);
		$this->setupLoggedUser($this->user);

		$this->controller->getOrganizationService()
			->expects($this->once())
			->method('findOrganization')
			->with('1234567890')
			->willReturn(null);

		$this->routeMatch->setParam('orgId', '1234567890');

		$result   = $this->controller->dispatch($this->request);
		$response = $this->controller->getResponse();
		
		$this->assertEquals(404, $response->getStatusCode());
	}

	public function testGetListWithNotAllowedOrganizationId()
	{
		$this->setupLoggedUser($this->user);

		$this->controller->getOrganizationService()
			->expects($this->once())
			->method('findOrganization')
			->with($this->organization->getId())
			->willReturn($this->organization);

		$this->routeMatch->setParam('orgId', $this->organization->getId());

		$result   = $this->controller->dispatch($this->request);
		$response = $this->controller->getResponse();

		$this->assertEquals(403, $response->getStatusCode());
	}

	public function testGetEmptyList()
	{
		$this->user->addMembership($this->organization);
		$this->setupLoggedUser($this->user);

		$this->controller->getOrganizationService()
			->expects($this->once())
			->method('findOrganization')
			->with($this->organization->getId())
			->willReturn($this->organization);

		$this->controller->getTaskService()
			->expects($this->once())
			->method('findTasks')
			->willReturn([]);

		$this->controller->getTaskService()
			->expects($this->once())
			->method('countOrganizationTasks')
			->with($this->organization)
			->willReturn(0);

		$this->routeMatch->setParam('orgId', $this->organization->getId());

		$result   = $this->controller->dispatch($this->request);
		$response = $this->controller->getResponse();

		$this->assertEquals(200, $response->getStatusCode());
		$arrayResult = json_decode($result->serialize(), true);
		$this->assertCount(0, $arrayResult['_embedded']['ora:task']);
		$this->assertNotEmpty($arrayResult['_links']['self']['href']);
		$this->assertArrayNotHasKey('next', $arrayResult['_links']);
		$this->assertEquals(0, $arrayResult['count']);
		$this->assertEquals(0, $arrayResult['total']);
	}

	public function testGetList()
	{
		$this->user->addMembership($this->organization);
		$this->setupLoggedUser($this->user);

		$this->controller->getOrganizationService()
			->expects($this->once())
			->method('findOrganization')
			->with($this->organization->getId())
			->willReturn($this->organization);
		
		$this->controller->getTaskService()
			->expects($this->once())
			->method('countOrganizationTasks')
			->with($this->organization)
			->willReturn(1);
		
		$task1 = new Task('1', $this->stream);
		$task1->setSubject('Lorem ipsum')
			->setCreatedBy($this->user)
			->setMostRecentEditBy($task1->getCreatedBy())
			->addMember($this->user, Task::ROLE_OWNER, $this->user, $task1->getCreatedAt());

		$this->controller->getTaskService()
			->expects($this->once())
			->method('findTasks')
			->willReturn([
				$task1
			]);

		$this->routeMatch->setParam('orgId', $this->organization->getId());

		$result   = $this->controller->dispatch($this->request);
		$response = $this->controller->getResponse();

		$this->assertEquals(200, $response->getStatusCode());
		$arrayResult = json_decode($result->serialize(), true);
		$this->assertCount(1, $arrayResult['_embedded']['ora:task']);
		$this->assertNotEmpty($arrayResult['_links']['self']['href']);
		$this->assertArrayNotHasKey('next', $arrayResult['_links']);
		$this->assertEquals(1, $arrayResult['count']);
		$this->assertEquals(1, $arrayResult['total']);

		$t = $arrayResult['_embedded']['ora:task'][0];
		$this->assertEquals('1', $t['id']);
		$this->assertEquals('Lorem ipsum', $t['subject']);
		$this->assertEquals('task', $t['type']);
		$this->assertEquals($task1->getStatus(), $t['status']);
		$this->assertEquals('John Doe', $t['createdBy']);
		$this->assertArrayHasKey($this->user->getId(), $t['members']);

		$m = $t['members'][$this->user->getId()];
		$this->assertEquals($this->user->getId(), $m['id']);
		$this->assertEquals(Task::ROLE_OWNER, $m['role']);
		$this->assertEquals('John', $m['firstname']);
		$this->assertEquals('Doe', $m['lastname']);
	}
	
	public function testGetListWithPagination(){
		
		$this->user->addMembership($this->organization);
		$this->setupLoggedUser($this->user);
		
		$this->controller->getOrganizationService()
		->expects($this->once())
		->method('findOrganization')
		->with($this->organization->getId())
		->willReturn($this->organization);
		
		$this->controller->getTaskService()
		->expects($this->once())
		->method('countOrganizationTasks')
		->with($this->organization)
		->willReturn(2);
		
		$task1 = new Task('1', $this->stream);
		$task1->setSubject('Lorem ipsum')
			->setCreatedAt(new \DateTime())
			->setCreatedBy($this->user)
			->setMostRecentEditAt($task1->getCreatedAt())
			->setMostRecentEditBy($task1->getCreatedBy())
			->addMember($this->user, Task::ROLE_OWNER, $this->user, $task1->getCreatedAt());
		
		$task2 = new Task('2', $this->stream);
		$task2->setSubject('dolor sit amet')
			->setCreatedAt(new \DateTime())
			->setCreatedBy($this->user)
			->setMostRecentEditAt($task2->getCreatedAt())
			->setMostRecentEditBy($task2->getCreatedBy())
			->addMember($this->user, Task::ROLE_OWNER, $this->user, $task2->getCreatedAt());
		
		$this->controller->getTaskService()
			->expects($this->once())
			->method('findTasks')
			->willReturn([
					$task1
			]);
		
		$this->routeMatch->setParam('orgId', $this->organization->getId());
		
		$params = $this->request->getQuery();
		$params->set('limit', 1);
		
		$result   = $this->controller->dispatch($this->request);
		$response = $this->controller->getResponse();
		
		$this->assertEquals(200, $response->getStatusCode());
		$arrayResult = json_decode($result->serialize(), true);
		$this->assertCount(1, $arrayResult['_embedded']['ora:task']);
		$this->assertNotEmpty($arrayResult['_links']['self']['href']);
		$this->assertNotEmpty($arrayResult['_links']['next']['href']);
		$this->assertEquals(1, $arrayResult['count']);
		$this->assertEquals(2, $arrayResult['total']);
	}

	public function testGetListWithDateInterval(){

		$this->user->addMembership($this->organization);
		$this->setupLoggedUser($this->user);

		$this->controller->getOrganizationService()
		->expects($this->once())
		->method('findOrganization')
		->with($this->organization->getId())
		->willReturn($this->organization);

		$this->controller->getTaskService()
		->expects($this->once())
		->method('countOrganizationTasks')
		->with($this->organization)
		->willReturn(2);

		$task1 = new Task('1', $this->stream);
		$task1->setSubject('Lorem ipsum')
		->setCreatedAt(new \DateTime('2000-01-01'))
		->setCreatedBy($this->user)
		->setMostRecentEditAt($task1->getCreatedAt())
		->setMostRecentEditBy($task1->getCreatedBy())
		->addMember($this->user, Task::ROLE_OWNER, $this->user, $task1->getCreatedAt());

		$task2 = new Task('2', $this->stream);
		$task2->setSubject('dolor sit amet')
		->setCreatedAt(new \DateTime('2015-12-31'))
		->setCreatedBy($this->user)
		->setMostRecentEditAt($task2->getCreatedAt())
		->setMostRecentEditBy($task2->getCreatedBy())
		->addMember($this->user, Task::ROLE_OWNER, $this->user, $task2->getCreatedAt());

		$task3 = new Task('3', $this->stream);
		$task3->setSubject('consectetur adipiscing elit')
		->setCreatedAt(new \DateTime('2015-10-01'))
		->setCreatedBy($this->user)
		->setMostRecentEditAt($task3->getCreatedAt())
		->setMostRecentEditBy($task3->getCreatedBy())
		->addMember($this->user, Task::ROLE_OWNER, $this->user, $task3->getCreatedAt());

		$this->controller->getTaskService()
		->expects($this->once())
		->method('findTasks')
		->willReturn([
				$task2,
				$task3
		]);

		$this->routeMatch->setParam('orgId', $this->organization->getId());

		$params = $this->request->getQuery();
		$params->set('endOn', '2015-12-31T00:00:00.000Z');

		$result   = $this->controller->dispatch($this->request);
		$response = $this->controller->getResponse();

		$this->assertEquals(200, $response->getStatusCode());
		$arrayResult = json_decode($result->serialize(), true);
		$this->assertCount(2, $arrayResult['_embedded']['ora:task']);
		$this->assertNotEmpty($arrayResult['_links']['self']['href']);
		$this->assertArrayNotHasKey('next', $arrayResult['_links']);
		$this->assertEquals(2, $arrayResult['count']);
		$this->assertEquals(2, $arrayResult['total']);
	}
	
	public function testGetEmptyListWithWrongFilter()
	{
		$this->user->addMembership($this->organization);
		$this->setupLoggedUser($this->user);
		
		$this->controller->getOrganizationService()
		->expects($this->once())
		->method('findOrganization')
		->with($this->organization->getId())
		->willReturn($this->organization);

		$this->routeMatch->setParam('orgId', $this->organization->getId());
		$params = $this->request->getQuery();
		$params->set('status', "Pippo");
		
		$result   = $this->controller->dispatch($this->request);
		$response = $this->controller->getResponse();
		
		$this->assertEquals(200, $response->getStatusCode());
		$arrayResult = json_decode($result->serialize(), true);
		$this->assertCount(0, $arrayResult['_embedded']['ora:task']);
		$this->assertNotEmpty($arrayResult['_links']['self']['href']);
		$this->assertArrayNotHasKey('next', $arrayResult['_links']);
		$this->assertEquals(0, $arrayResult['count']);
		$this->assertEquals(0, $arrayResult['total']);
	}
	
	public function testGetEmptyListWithFiltering()
	{
		$this->user->addMembership($this->organization);
		$this->setupLoggedUser($this->user);
	
		$this->controller->getOrganizationService()
		->expects($this->once())
		->method('findOrganization')
		->with($this->organization->getId())
		->willReturn($this->organization);
	
		$this->controller->getTaskService()
		->expects($this->once())
		->method('findTasks')
		->willReturn([]);
	
		$this->controller->getTaskService()
		->expects($this->once())
		->method('countOrganizationTasks')
		->with($this->organization)
		->willReturn(0);
	
		$this->routeMatch->setParam('orgId', $this->organization->getId());
		$params = $this->request->getQuery();
		$params->set('status', Task::STATUS_ONGOING);
	
		$result   = $this->controller->dispatch($this->request);
		$response = $this->controller->getResponse();
	
		$this->assertEquals(200, $response->getStatusCode());
		$arrayResult = json_decode($result->serialize(), true);
		$this->assertCount(0, $arrayResult['_embedded']['ora:task']);
		$this->assertNotEmpty($arrayResult['_links']['self']['href']);
		$this->assertArrayNotHasKey('next', $arrayResult['_links']);
		$this->assertEquals(0, $arrayResult['count']);
		$this->assertEquals(0, $arrayResult['total']);
	}
	
	public function testGetListWithFiltering()
	{
		$this->user->addMembership($this->organization);
		$this->setupLoggedUser($this->user);
		
		$this->controller->getOrganizationService()
		->expects($this->once())
		->method('findOrganization')
		->with($this->organization->getId())
		->willReturn($this->organization);
		
		$this->controller->getTaskService()
		->expects($this->once())
		->method('countOrganizationTasks')
		->with($this->organization)
		->willReturn(2);
		
		$task1 = new Task('1', $this->stream);
		$task1->setStatus(Task::STATUS_OPEN);
		$task1->setSubject('Lorem ipsum')
		->setCreatedBy($this->user)
		->setMostRecentEditBy($task1->getCreatedBy())
		->addMember($this->user, Task::ROLE_OWNER, $this->user, $task1->getCreatedAt());
		
		$task2 = new Task('2', $this->stream);
		$task2->setStatus(Task::STATUS_OPEN);
		$task2->setSubject('Lorem ipsum 2')
		->setCreatedBy($this->user)
		->setMostRecentEditBy($task2->getCreatedBy())
		->addMember($this->user, Task::ROLE_OWNER, $this->user, $task2->getCreatedAt());
		
		$this->controller->getTaskService()
		->expects($this->once())
		->method('findTasks')
		->willReturn([
				$task1,
				$task2
		]);
		
		$this->routeMatch->setParam('orgId', $this->organization->getId());
		
		$params = $this->request->getQuery();
		$params->set('status', Task::STATUS_OPEN);
		
		$result   = $this->controller->dispatch($this->request);
		$response = $this->controller->getResponse();
		
		$this->assertEquals(200, $response->getStatusCode());
		$arrayResult = json_decode($result->serialize(), true);
		$this->assertCount(2, $arrayResult['_embedded']['ora:task']);
		$this->assertNotEmpty($arrayResult['_links']['self']['href']);
		$this->assertArrayNotHasKey('next', $arrayResult['_links']);
		$this->assertEquals(2, $arrayResult['count']);
		$this->assertEquals(2, $arrayResult['total']);
		
		$t1 = $arrayResult['_embedded']['ora:task'][0];
		$this->assertEquals(Task::STATUS_OPEN, $t1['status']);
		
		$t2 = $arrayResult['_embedded']['ora:task'][1];
		$this->assertEquals(Task::STATUS_OPEN, $t2['status']);
	}
}
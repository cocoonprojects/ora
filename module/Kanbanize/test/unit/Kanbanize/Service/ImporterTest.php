<?php

namespace Kanbanize\Service;

use Application\Entity\User;
use Application\Service\UserService;
use Kanbanize\Entity\KanbanizeTask as ReadModelKanbanizeTask;
use Kanbanize\Entity\KanbanizeStream as ReadModelKanbanizeStream;
use Kanbanize\KanbanizeStream;
use Kanbanize\KanbanizeTask;
use People\Organization;
use People\Entity\Organization as ReadModelOrganization;
use People\Service\OrganizationService;
use Prooph\EventStoreTest\TestCase;
use Rhumsaa\Uuid\Uuid;
use TaskManagement\Service\StreamService;
use TaskManagement\Service\TaskService;
use TaskManagement\Task;
use TaskManagement\Stream;

class ImporterTest extends TestCase {

	private $kanbanizeServiceStub;
	private $taskServiceStub;
	private $streamServiceStub;
	private $userServiceStub;
	private $organization;
	private $requestedBy;
	private $apiMock;

	protected function setup(){
		parent::setUp();
		$tasks = [
				'114' => ['taskid' => 114, 'columnname'=> "WIP", 'title' => "A", 'assignee' => 'None'],
		];
		$boards = [
				'010' => ['id' => '010', 'name' => 'Board 001'],
		];
		$projects = [
				'01' => ['id' => '01', 'name' => 'Project 01', 'boards' => $boards],
		];

		$this->kanbanizeServiceStub = $this->getMockBuilder(KanbanizeService::class)->getMock();;
		$this->taskServiceStub = $this->getMockBuilder(TaskService::class)->getMock();;
		$this->streamServiceStub = $this->getMockBuilder(StreamService::class)->getMock();;
		$this->userServiceStub = $this->getMockBuilder(UserService::class)->getMock();
		$this->requestedBy = User::create();
		$this->organization = Organization::create("Kanbanize in sync Organization", $this->requestedBy);
		$this->organization->setSetting("kanbanizeColumnMapping", [
			"MARKET IDEA" => Task::STATUS_IDEA,
			"PRODUCT MANAGER APPROVED" => Task::STATUS_IDEA,
			"ESTIMATION" => Task::STATUS_OPEN,
			"BACKLOG" => Task::STATUS_OPEN,
			"WIP" => Task::STATUS_ONGOING,
			"TESTING" => Task::STATUS_COMPLETED,
			"USER ACCEPTANCE" => Task::STATUS_COMPLETED,
			"PRODUCTION" => Task::STATUS_ACCEPTED,
			"CLOSED" => Task::STATUS_CLOSED
		], $this->requestedBy);
		
		$this->organizationServiceStub = $this->getMockBuilder(OrganizationService::class)->getMock();
		$this->apiMock = new KanbanizeAPIMock($tasks, [], $projects);
		$organization = new ReadModelOrganization($this->organization->getId());
	}

	public function testImportProjects(){
		$this->taskServiceStub->expects($this->atLeastOnce())
			->method('findTasks')
			->willReturn([]);
		$importer = new Importer($this->kanbanizeServiceStub,
				$this->taskServiceStub,
				$this->streamServiceStub,
				$this->eventStore,
				$this->userServiceStub,
				$this->organization,
				$this->requestedBy,
				$this->apiMock);
		$importer->importProjects();
		$importResult = $importer->getImportResult();
		$this->assertArrayHasKey('createdStreams', $importResult);
		$this->assertArrayHasKey('createdTasks', $importResult);
		$this->assertArrayHasKey('deletedTasks', $importResult);
		$this->assertArrayHasKey('updatedTasks', $importResult);
		$this->assertArrayHasKey('errors', $importResult);
		$this->assertEquals(1, $importResult['createdStreams']);
		$this->assertEquals(1, $importResult['createdTasks']);
		$this->assertEquals(0, $importResult['deletedTasks']);
		$this->assertEquals(0, $importResult['updatedTasks']);
		$this->assertEmpty($importResult['errors']);
	}
	
	public function testUpdateStreamAndTasks(){
		$stream = KanbanizeStream::create($this->organization, "a new Stream", $this->requestedBy, [
				'boardId' => '010',
				'projectId' => '01'
		]);
		$organization = new ReadModelOrganization($this->organization->getId());
		$readModelStream = new ReadModelKanbanizeStream($stream->getId(), $organization);
		$task = KanbanizeTask::create($stream, "a new task", $this->requestedBy, [
				'taskid'=> 114,
				'columnname' => "Testing",
				'status' => Task::STATUS_COMPLETED
		]);
		$task->setSubject("Z", $this->requestedBy);
		$readModelTask = new ReadModelKanbanizeTask($task->getId(), $readModelStream);
		$this->kanbanizeServiceStub->expects($this->atLeastOnce())
			->method('findByTaskId')
			->willReturn($readModelTask);
		$this->kanbanizeServiceStub->expects($this->atLeastOnce())
			->method('findStreamByBoardId')
			->willReturn($readModelStream);
		$this->taskServiceStub->expects($this->atLeastOnce())
			->method('getTask')
			->willReturn($task);
		$this->taskServiceStub->expects($this->atLeastOnce())
			->method('findTasks')
			->willReturn([$readModelTask]);
		$this->streamServiceStub->expects($this->atLeastOnce())
			->method('getStream')
			->willReturn($stream);
		
		$importer = new Importer($this->kanbanizeServiceStub,
				$this->taskServiceStub,
				$this->streamServiceStub,
				$this->eventStore,
				$this->userServiceStub,
				$this->organization,
				$this->requestedBy,
				$this->apiMock,
				$this->organizationServiceStub);
		$importer->importProjects();
		$importResult = $importer->getImportResult();
		$this->assertArrayHasKey('createdStreams', $importResult);
		$this->assertArrayHasKey('updatedStreams', $importResult);
		$this->assertArrayHasKey('createdTasks', $importResult);
		$this->assertArrayHasKey('deletedTasks', $importResult);
		$this->assertArrayHasKey('updatedTasks', $importResult);
		$this->assertArrayHasKey('errors', $importResult);
		$this->assertEquals(0, $importResult['createdStreams']);
		$this->assertEquals(1, $importResult['updatedStreams']);
		$this->assertEquals(0, $importResult['createdTasks']);
		$this->assertEquals(0, $importResult['deletedTasks']);
		$this->assertEquals(1, $importResult['updatedTasks']);
		$this->assertEquals("A", $task->getSubject());
		$this->assertEquals(Task::STATUS_ONGOING, $task->getStatus());
		$this->assertEquals("WIP", $task->getColumnName());
		$this->assertEmpty($importResult['errors']);
	}
}
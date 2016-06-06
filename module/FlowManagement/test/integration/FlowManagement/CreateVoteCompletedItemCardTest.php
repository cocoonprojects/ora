<?php
namespace FlowManagement;

use IntegrationTest\Bootstrap;
use TaskManagement\Task;
use TaskManagement\Service\TaskService;
use TaskManagement\Stream;
use Rhumsaa\Uuid\Uuid;

class CreateVoteCompletedItemCardTest extends \PHPUnit_Framework_TestCase{
	
	/**
	 * @var FlowService
	 */
	protected $flowService;
	/**
	 * @var EventStore
	 */
	protected $transactionManager;
	/**
	 * @var TaskService
	 */
	protected $taskService;
	/**
	 * @var Stream
	 */
	protected $stream;
	protected $owner;
	protected $member;
	
	protected function setUp(){
		$serviceManager = Bootstrap::getServiceManager();
		
		$this->flowService = $serviceManager->get('FlowManagement\FlowService');
		$this->transactionManager = $serviceManager->get('prooph.event_store');
		$this->taskService = $serviceManager->get('TaskManagement\TaskService');
		
		$userService = $serviceManager->get('Application\UserService');
		$this->owner = $userService->findUser('60000000-0000-0000-0000-000000000000');
		$this->member = $userService->findUser('80000000-0000-0000-0000-000000000000');
		
		$streamService = $serviceManager->get('TaskManagement\StreamService');
		$this->stream = $streamService->getStream('00000000-1000-0000-0000-000000000000');
	}
	
	public function testCreateCompletedItemVoteCard(){

		$previousCompletedItems = $this->taskService->findTasks(Uuid::fromString($this->stream->getOrganizationId()), null, null, ['status' => Task::STATUS_COMPLETED]);

		$this->transactionManager->beginTransaction();
		try {
			$task = Task::create($this->stream, "foo stream", $this->owner);
			$task->setDescription("a very useful description", $this->owner);

			$task->open($this->owner);
			$task->addMember($this->owner, Task::ROLE_OWNER);
			$task->execute($this->owner);
			$task->addEstimation(1, $this->owner);
			$task->complete($this->owner);

			$this->taskService->addTask($task);
			$this->transactionManager->commit();
		}catch (\Exception $e) {
			var_dump($e);
			$this->transactionManager->rollback();
			throw $e;
		}
		$ownerFlowCards = $this->flowService->findFlowCards($this->owner, null, null, null);
		$memberFlowCards = $this->flowService->findFlowCards($this->member, null, null, null);
		$newCompletedItems = $this->taskService->findTasks(Uuid::fromString($this->stream->getOrganizationId()), null, null, ['status' => Task::STATUS_COMPLETED]);

		$newCompletedItemsCount = count($newCompletedItems) - count($previousCompletedItems);
		
		$this->assertEquals(Task::STATUS_COMPLETED, $task->getStatus());
		$this->assertNotEmpty($ownerFlowCards);
		$this->assertNotEmpty($memberFlowCards);
		$this->assertEquals($newCompletedItemsCount, count($memberFlowCards));
		$this->assertEquals($newCompletedItemsCount+1, count($ownerFlowCards));
	}
}
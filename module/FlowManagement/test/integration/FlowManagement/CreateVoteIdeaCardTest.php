<?php
namespace FlowManagement;

use IntegrationTest\Bootstrap;
use TaskManagement\Task;
use TaskManagement\Service\TaskService;
use TaskManagement\Stream;
use Rhumsaa\Uuid\Uuid;

class CreateVoteIdeaCardTest extends \PHPUnit_Framework_TestCase{
	
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
		$this->member = $userService->findUser('70000000-0000-0000-0000-000000000000');
		
		$streamService = $serviceManager->get('TaskManagement\StreamService');
		$this->stream = $streamService->getStream('00000000-1000-0000-0000-000000000000');
	}
	
	public function testCreateIdeaVoteCard(){
		$this->transactionManager->beginTransaction();
		try {
			$item = Task::create($this->stream, "foo stream", $this->owner);
			$item->setDescription("a very useful description", $this->owner);
			$this->taskService->addTask($item);
			$this->transactionManager->commit();
		}catch (\Exception $e) {
			var_dump($e);
			$this->transactionManager->rollback();
			throw $e;
		}
		
		
		$ownerFlowCards = $this->flowService->findFlowCards($this->owner, null, null, null);
		$memberFlowCards = $this->flowService->findFlowCards($this->member, null, null, null);
		$newItemIdeas = $this->taskService->findTasks(Uuid::fromString($this->stream->getOrganizationId()), null, null, ['status' => Task::STATUS_IDEA]);
		$this->assertNotEmpty($ownerFlowCards);
		$this->assertNotEmpty($memberFlowCards);
		$this->assertEquals(count($newItemIdeas), count($memberFlowCards));
		$this->assertEquals(count($newItemIdeas), count($ownerFlowCards));
	}
}
<?php
namespace TaskManagement\Service;

use Prooph\EventStoreTest\TestCase;
use Prooph\EventStore\Stream\Stream as ProophStream;
use Prooph\EventStore\Stream\StreamName;
use Rhumsaa\Uuid\Uuid;
use Application\Entity\User;
use People\Organization;
use TaskManagement\Stream;
use TaskManagement\Task;
use Zend\View\Renderer\PhpRenderer;
use TaskManagement;


class EventSourcingTaskServiceTest extends TestCase {
	
	/**
	 * 
	 * @var TaskService
	 */
	private $taskService;
	/**
	 * 
	 * @var User
	 */
	private $user;

	
	protected function setUp() {
		parent::setUp();
		$entityManager = $this->getMock('\Doctrine\ORM\EntityManager', array('getRepository', 'getClassMetadata', 'persist', 'flush'), array(), '', false);
		$this->eventStore->beginTransaction();
		$this->eventStore->create(new ProophStream(new StreamName('event_task'), array()));
		$this->eventStore->commit();
		$this->taskService = new EventSourcingTaskService($this->eventStore, $entityManager);
		$this->user = User::create();		
	}
	
	public function testFindMembersWithEmptyShares(){
		
		$taskStub = $this->getMockBuilder(TaskManagement\Entity\Task::class)
			->disableOriginalConstructor()
			->getMock();
		
		$memberStub = $this->getMockBuilder(TaskManagement\Entity\TaskMember::class)
			->disableOriginalConstructor()
			->getMock();
		
		$memberStub->method('getShare')->willReturn(0);		
		$taskStub->method('getMembers')->willReturn(array($memberStub));
		
		$membersWithEmptyShares = $this->taskService->findMembersWithEmptyShares($taskStub);
		
		$this->assertNotEmpty($membersWithEmptyShares);
		$this->assertEquals(1, count($membersWithEmptyShares));
		
	}
	
	protected function setupStream(){
		
		$organization = Organization::create('My brand new Orga', $this->user);
        return Stream::create($organization, 'Really useful stream', $this->user);
	}
	
	protected function setupTask(){
		
		$stream = $this->setupStream();
		return Task::create($stream, 'task subject', $this->user);		
	}
	
}

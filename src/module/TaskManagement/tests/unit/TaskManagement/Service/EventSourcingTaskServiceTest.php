<?php
namespace TaskManagement\Service;

use Prooph\EventStoreTest\TestCase;
use Prooph\EventStore\Stream\Stream as ProophStream;
use Prooph\EventStore\Stream\StreamName;
use Rhumsaa\Uuid\Uuid;
use Application\Entity\User;
use Application\Organization;
use TaskManagement\Stream;
use TaskManagement\Task;

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
	
	public function testNotifyMembersForShareAssigment() {
		
		$taskToNotify = $this->setupTask();
		$this->user->setEmail('user@email.com');
		
		$taskToNotify->addMember($this->user, Task::ROLE_OWNER);
		$taskToNotify->addEstimation(1, $this->user);
		$taskToNotify->complete($this->user);
		$taskToNotify->accept($this->user);		
		
		$this->taskService->notifyMembersForShareAssignment($taskToNotify);
		
		//TODO: completare il test con le assertions
		
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

<?php
namespace TaskManagement;

use Rhumsaa\Uuid\Uuid;
use Application\Entity\User;
use People\Organization;

class TaskTest extends \PHPUnit_Framework_TestCase {
	
	/**
	 * 
	 * @var User
	 */	
	protected $owner;
	/**
	 * @var User
	 */
	protected $user1;
	/**
	 * @var User
	 */
	protected $user2;
	/**
	 * @var User
	 */
	protected $user3;
	/**
	 * 
	 * @var Stream
	 */
	protected $stream;

	protected function setUp() {
		$this->owner = User::create();
		$this->user1 = User::create();
		$this->user2 = User::create();
		$this->user3 = User::create();

		$organization = Organization::create('Pellentesque lorem ligula, auctor ac', $this->owner);
		$this->owner->addMembership($organization);
		$organization->addMember($this->user1);
		$this->user1->addMembership($organization);
		$organization->addMember($this->user2);
		$this->user2->addMembership($organization);
		$organization->addMember($this->user3);
		$this->user3->addMembership($organization);

		$this->stream = Stream::create($organization, 'Curabitur rhoncus mattis massa vel', $this->owner);
	}

	public function testCreate() {
		$task = Task::create($this->stream, 'Test subject', $this->owner);
		$this->assertNotNull($task->getId());
		$this->assertEquals('Test subject', $task->getSubject());
		$this->assertEquals(Task::STATUS_ONGOING, $task->getStatus());
		$this->assertEquals($this->stream->getId(), $task->getStreamId());
	}
	
	public function testCreateWithNoSubject() {
		$task = Task::create($this->stream, null, $this->owner);
		$this->assertNotNull($task->getId());
		$this->assertNull($task->getSubject());
		$this->assertEquals(Task::STATUS_ONGOING, $task->getStatus());
		$this->assertEquals($this->stream->getId(), $task->getStreamId());
	}
	
	public function testCreateWorkItemIdea(){
		$options = array('status'=>Task::STATUS_IDEA);
		$task = Task::create($this->stream, 'Work Item Idea subject', $this->owner, $options);
		$this->assertNotNull($task->getId());
		$this->assertEquals('Work Item Idea subject', $task->getSubject());
		$this->assertEquals(Task::STATUS_IDEA, $task->getStatus());
		$this->assertEquals($this->stream->getId(), $task->getStreamId());
	}
	
	public function testCreateWorkItemIdeaWithNoSubject(){
		$options = array('status'=>Task::STATUS_IDEA);
		$task = Task::create($this->stream, null, $this->owner, $options);
		$this->assertNotNull($task->getId());
		$this->assertNull($task->getSubject());
		$this->assertEquals(Task::STATUS_IDEA, $task->getStatus());
		$this->assertEquals($this->stream->getId(), $task->getStreamId());
	}
	
	/**
	 * @expectedException Application\IllegalStateException
	 */
	public function testDeleteOngoingTask() {
		$task = Task::create($this->stream, null, $this->owner);
		$task->delete($this->owner);
		
		$this->assertEquals(Task::STATUS_DELETED, $task->getStatus());
		$task->complete($this->owner);
	}
	
	/**
	 * @expectedException Application\IllegalStateException
	 */
	public function testDeleteCompletedTask() {
		$task = Task::create($this->stream, null, $this->owner);
		$task->addMember($this->owner, Task::ROLE_OWNER);
		$task->complete($this->owner);
		$task->delete($this->owner);
	}
	
	public function testAddMember() {
		$task = Task::create($this->stream, null, $this->owner);
		$task->addMember($this->owner);
		
		$members = $task->getMembers();
		$this->assertArrayHasKey($this->owner->getId(), $members);
		$this->assertEquals(Task::ROLE_MEMBER, $members[$this->owner->getId()]['role']);
		$this->assertArrayNotHasKey('accountId', $members[$this->owner->getId()]);
	}
	
	public function testAddMembers() {
		$task = Task::create($this->stream, null, $this->owner);
		$task->addMember($this->owner, Task::ROLE_OWNER);
		$task->addMember($this->user1);
		$task->addMember($this->user2);
		
		$members = $task->getMembers();
		$this->assertCount(3, $members);
		$this->assertArrayHasKey($this->owner->getId(), $members);
		$this->assertArrayHasKey($this->user1->getId(), $members);
		$this->assertArrayHasKey($this->user2->getId(), $members);
		$this->assertEquals(Task::ROLE_OWNER, $members[$this->owner->getId()]['role']);
		$this->assertEquals(Task::ROLE_MEMBER, $members[$this->user1->getId()]['role']);
		$this->assertEquals(Task::ROLE_MEMBER, $members[$this->user2->getId()]['role']);
		$this->assertArrayNotHasKey('accountId', $members[$this->owner->getId()]);
		$this->assertArrayNotHasKey('accountId', $members[$this->user1->getId()]);
		$this->assertArrayNotHasKey('accountId', $members[$this->user2->getId()]);
	}
	
	public function testAddAdmin() {
		$task = Task::create($this->stream, null, $this->owner);
		$task->addMember($this->owner, Task::ROLE_OWNER);
		
		$members = $task->getMembers();
		$this->assertArrayHasKey($this->owner->getId(), $members);
		$this->assertEquals(Task::ROLE_OWNER, $members[$this->owner->getId()]['role']);
		$this->assertArrayNotHasKey('accountId', $members[$this->owner->getId()]);
	}

	/**
	 * @expectedException People\MissingOrganizationMembershipException
	 */
	public function testAddMemberNotInOrganization() {
		$task = Task::create($this->stream, null, $this->owner);
		$user = User::create();
		$task->addMember($user);
	}

	public function testHasMember() {
		$task = Task::create($this->stream, null, $this->owner);
		$task->addMember($this->owner);
		$this->assertTrue($task->hasMember($this->owner));
	}
	
	public function testHasAs() {
		$task = Task::create($this->stream, null, $this->owner);
		$task->addMember($this->owner);
		$this->assertTrue($task->hasAs(Task::ROLE_MEMBER, $this->owner));
	}
	
	public function testAddEstimation() {
		$task = Task::create($this->stream, null, $this->owner);
		$task->addMember($this->user1);
		$task->addMember($this->user2);
		
		$task->addEstimation(20, $this->user1);
		$task->addEstimation(1000, $this->user2);
		
		$members = $task->getMembers();
		
		$this->assertArrayHasKey($this->user1->getId(), $members);
		$this->assertArrayHasKey($this->user2->getId(), $members);
		$this->assertEquals(20, $members[$this->user1->getId()]['estimation']);
		$this->assertEquals(1000, $members[$this->user2->getId()]['estimation']);
	}

	public function testClose() {
		$task = Task::create($this->stream, null, $this->owner);
		$task->addMember($this->owner, Task::ROLE_OWNER);
		$task->addEstimation(1, $this->owner);
		$task->complete($this->owner);
		$task->accept($this->owner, new \DateInterval('P7D'));
		$task->close($this->owner);
		$this->assertEquals(Task::STATUS_CLOSED, $task->getStatus());
	}

	/**
	 * @expectedException Application\IllegalStateException
	 */
	public function testCompleteWithNoEstimation() {
		$task = Task::create($this->stream, null, $this->owner);
		$task->addMember($this->owner, Task::ROLE_OWNER);
		$task->addMember($this->user1);
		$task->addMember($this->user2);
		$task->complete($this->owner);
	}

	/**
	 * @expectedException Application\IllegalStateException
	 */
	public function testCompleteWithOneEstimation() {
		$task = Task::create($this->stream, null, $this->owner);
		$task->addMember($this->owner, Task::ROLE_OWNER);
		$task->addMember($this->user1);
		$task->addMember($this->user2);
		$task->addEstimation(1, $this->owner);
		$task->complete($this->owner);
	}

	public function testCompleteWithThreeEstimation() {
		$task = Task::create($this->stream, null, $this->owner);
		$task->addMember($this->owner, Task::ROLE_OWNER);
		$task->addMember($this->user1);
		$task->addMember($this->user2);
		$task->addEstimation(1, $this->owner);
		$task->addEstimation(11, $this->user1);
		$task->addEstimation(6, $this->user2);
		$task->complete($this->owner);
		$this->assertEquals(Task::STATUS_COMPLETED, $task->getStatus());
		$this->assertEquals(6, $task->getAverageEstimation());
	}
}
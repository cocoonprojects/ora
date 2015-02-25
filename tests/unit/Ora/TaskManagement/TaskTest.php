<?php
namespace Ora\TaskManagement;

use DoctrineORMModule\Proxy\__CG__\Ora\ReadModel\Organization;

use Ora\StreamManagement\Stream;
use Rhumsaa\Uuid\Uuid;
use Ora\User\User;
use Ora\ReadModel\Organization as ReadModelOrganization;

class TaskTest extends \PHPUnit_Framework_TestCase {
	
	/**
	 * 
	 * @var Task
	 */
	protected $task;
	
	/**
	 * 
	 * @var User
	 */	
	protected $taskCreator;
	
	/**
	 * 
	 * @var Stream
	 */
	protected $stream;
	
	/**
	 * @var Organization
	 */
	protected $organization;
	
	
	protected function setUp() {
		$this->taskCreator = User::create();
		$this->organization = new ReadModelOrganization(Uuid::fromString('00000000-1000-0000-0000-000000000022'), new \DateTime(), $this->taskCreator);		
		$this->stream = new Stream(Uuid::fromString('00000000-1000-0000-0000-000000000002'), $this->taskCreator, $this->organization);
		$this->task = Task::create($this->stream, 'test', $this->taskCreator);

	}
		
	public function testCreate() {
		$task = Task::create($this->stream, 'Test subject', $this->taskCreator);
		$this->assertInstanceOf('Rhumsaa\Uuid\Uuid', $task->getId());
		$this->assertEquals('Test subject', $task->getSubject());
		$this->assertEquals(Task::STATUS_ONGOING, $task->getStatus());
		$this->assertEquals($this->stream->getId()->toString(), $task->getStreamId());
		$this->assertTrue($task->hasMember($this->taskCreator));
		$this->assertTrue($task->hasAs(Task::ROLE_OWNER, $this->taskCreator));
	}
	
	public function testCreateWithNoSubject() {
		$task = Task::create($this->stream, null, $this->taskCreator);
		$this->assertInstanceOf('Rhumsaa\Uuid\Uuid', $task->getId());
		$this->assertNull($task->getSubject());
		$this->assertEquals(Task::STATUS_ONGOING, $task->getStatus());
		$this->assertEquals($this->stream->getId()->toString(), $task->getStreamId());
		$this->assertTrue($task->hasMember($this->taskCreator));
		$this->assertTrue($task->hasAs(Task::ROLE_OWNER, $this->taskCreator));
	}
	
	public function testAddEstimation() {
		$user1 = User::create();
		$user2 = User::create();
		
		$this->task->addMember($user1, $user1);
		$this->task->addMember($user2, $user2);
		
		$this->task->addEstimation(20, $user1);
		$this->task->addEstimation(1000, $user2);
		
		$members = $this->task->getMembers();
		
		$this->assertArrayHasKey($user1->getId(), $members);
		$this->assertArrayHasKey($user2->getId(), $members);
		$this->assertCount(3, $members);
		$this->assertEquals(20, $members[$user1->getId()]['estimation']);
		$this->assertEquals(1000, $members[$user2->getId()]['estimation']);
	}
	
	public function testAssignShare() {
		$user1 = User::create();
		$user2 = User::create();
		
		$this->task->addMember($user1, $user1);
		$this->task->addMember($user2, $user2);
		
		$this->task->complete($this->taskCreator);
		
		$this->task->addEstimation(1000, $user1);
		$this->task->addEstimation(2500, $user2);
		$this->task->addEstimation(3200, $this->taskCreator);
		
		$this->task->accept($this->taskCreator);
		
		$this->task->assignShares([
			$this->taskCreator->getId() => 40,
			$user1->getId()				=> 40,
			$user2->getId()				=> 20
		], $this->taskCreator);
		
		$this->assertEquals(40, $this->task->getMembers()[$this->taskCreator->getId()]['share']);
		$this->assertEquals(40, $this->task->getMembers()[$user1->getId()]['share']);
		$this->assertEquals(20, $this->task->getMembers()[$user2->getId()]['share']);
	}
	
	public function testEveryMemberAssignShares() {
		$user1 = User::create();
		$user2 = User::create();
		
		$this->task->addMember($user1, $user1);
		$this->task->addMember($user2, $user2);
		
		$this->task->complete($this->taskCreator);
		
		$this->task->addEstimation(1000, $user1);
		$this->task->addEstimation(2500, $user2);
		$this->task->addEstimation(3200, $this->taskCreator);
		
		$this->task->accept($this->taskCreator);
		
		$this->task->assignShares([
			$this->taskCreator->getId() => 40,
			$user1->getId()				=> 40,
			$user2->getId()				=> 20
		], $this->taskCreator);
		
		$this->task->assignShares([
			$this->taskCreator->getId() => 33,
			$user1->getId()				=> 18,
			$user2->getId()				=> 49
		], $user1);
		
		$this->task->assignShares([
			$this->taskCreator->getId() => 23,
			$user1->getId()				=> 54,
			$user2->getId()				=> 23
		], $user2);
		
		$this->assertEquals(32, $this->task->getMembers()[$this->taskCreator->getId()]['share']);
		$this->assertEquals(37.33, $this->task->getMembers()[$user1->getId()]['share']);
		$this->assertEquals(30.67, $this->task->getMembers()[$user2->getId()]['share']);
	}

	public function testEveryMemberAssignSharesWithAMemberTo0() {
		$user1 = User::create();
		$user2 = User::create();
		
		$this->task->addMember($user1, $user1);
		$this->task->addMember($user2, $user2);
		
		$this->task->complete($this->taskCreator);
		
		$this->task->addEstimation(1000, $user1);
		$this->task->addEstimation(2500, $user2);
		$this->task->addEstimation(3200, $this->taskCreator);
		
		$this->task->accept($this->taskCreator);
		
		$this->task->assignShares([
			$this->taskCreator->getId() => 40,
			$user1->getId()				=> 60,
			$user2->getId()				=> 0
		], $this->taskCreator);
		
		$this->task->assignShares([
			$this->taskCreator->getId() => 33,
			$user1->getId()				=> 67,
			$user2->getId()				=> 0
		], $user1);
		
		$this->task->assignShares([
			$this->taskCreator->getId() => 23,
			$user1->getId()				=> 77,
			$user2->getId()				=> 0
		], $user2);
		
		$this->assertEquals(32, $this->task->getMembers()[$this->taskCreator->getId()]['share']);
		$this->assertEquals(68, $this->task->getMembers()[$user1->getId()]['share']);
		$this->assertEquals(0, $this->task->getMembers()[$user2->getId()]['share']);
	}

	public function testEveryMemberAssignSharesWith0() {
		$user1 = User::create();
		$user2 = User::create();
		
		$this->task->addMember($user1, $user1);
		$this->task->addMember($user2, $user2);
		
		$this->task->complete($this->taskCreator);
		
		$this->task->addEstimation(1000, $user1);
		$this->task->addEstimation(2500, $user2);
		$this->task->addEstimation(3200, $this->taskCreator);
		
		$this->task->accept($this->taskCreator);
		
		$this->task->assignShares([
			$this->taskCreator->getId() => 40,
			$user1->getId()				=> 60,
			$user2->getId()				=> 0
		], $this->taskCreator);
		
		$this->task->assignShares([
			$this->taskCreator->getId() => 33,
			$user1->getId()				=> 43,
			$user2->getId()				=> 24
		], $user1);
		
		$this->task->assignShares([
			$this->taskCreator->getId() => 23,
			$user1->getId()				=> 77,
			$user2->getId()				=> 0
		], $user2);
		
		$this->assertEquals(32, $this->task->getMembers()[$this->taskCreator->getId()]['share']);
		$this->assertEquals(60, $this->task->getMembers()[$user1->getId()]['share']);
		$this->assertEquals(8, $this->task->getMembers()[$user2->getId()]['share']);
	}
	
	public function testEveryMemberAssignSharesWithASkip() {
		$user1 = User::create();
		$user2 = User::create();
	
		$this->task->addMember($user1, $user1);
		$this->task->addMember($user2, $user2);
	
		$this->task->complete($this->taskCreator);
	
		$this->task->addEstimation(1000, $user1);
		$this->task->addEstimation(2500, $user2);
		$this->task->addEstimation(3200, $this->taskCreator);
	
		$this->task->accept($this->taskCreator);
	
		$this->task->skipShares($this->taskCreator);
	
		$this->task->assignShares([
				$this->taskCreator->getId() => 33,
				$user1->getId()				=> 39,
				$user2->getId()				=> 28
		], $user1);
	
		$this->task->assignShares([
				$this->taskCreator->getId() => 23,
				$user1->getId()				=> 77,
				$user2->getId()				=> 0
		], $user2);
	
		$this->assertEquals(28, $this->task->getMembers()[$this->taskCreator->getId()]['share']);
		$this->assertEquals(58, $this->task->getMembers()[$user1->getId()]['share']);
		$this->assertEquals(14, $this->task->getMembers()[$user2->getId()]['share']);
	}
	
	public function testLastUserShareAssignement() {
		$user1 = User::create();
		$user2 = User::create();
		
		$this->task->addMember($user1, $user1);
		$this->task->addMember($user2, $user2);
		
		$this->task->complete($this->taskCreator);
		
		$this->task->addEstimation(1000, $user1);
		$this->task->addEstimation(2500, $user2);
		$this->task->addEstimation(3200, $this->taskCreator);
		
		$this->task->accept($this->taskCreator);
		
		$this->task->skipShares($this->taskCreator);
		
		$this->task->assignShares([
				$this->taskCreator->getId() => 33,
				$user1->getId()				=> 39,
				$user2->getId()				=> 28
		], $user1);
		
		$this->task->assignShares([
				$this->taskCreator->getId() => 23,
				$user1->getId()				=> 77,
				$user2->getId()				=> 0
		], $user2);
		
		$this->assertEquals(Task::STATUS_CLOSED, $this->task->getStatus());

	}
}
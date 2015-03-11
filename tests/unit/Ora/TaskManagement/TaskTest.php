<?php
namespace Ora\TaskManagement;

use Ora\StreamManagement\Stream;
use Rhumsaa\Uuid\Uuid;
use Ora\User\User;
use Ora\Organization\Organization;

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
	 * @var Organization
	 */
	protected $organization;
	
	
	/**
	 * 
	 * @var Stream
	 */
	protected $stream;
		
	
	protected function setUp() {
		$this->taskCreator = User::create();
		$organization = Organization::create('Pellentesque lorem ligula, auctor ac', $this->taskCreator);
		$this->stream = Stream::create($organization, 'Curabitur rhoncus mattis massa vel', $this->taskCreator);
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

		$stream = new Stream(Uuid::fromString('00000000-1000-0000-0000-000000000002'), $this->taskCreator, $this->organization);
		$this->task = Task::create($stream, 'test', $this->taskCreator);

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
			$this->taskCreator->getId() => 0.4,
			$user1->getId()				=> 0.4,
			$user2->getId()				=> 0.2
		], $this->taskCreator);
		
		$this->assertEquals(0.4, $this->task->getMembers()[$this->taskCreator->getId()]['share']);
		$this->assertEquals(0.4, $this->task->getMembers()[$user1->getId()]['share']);
		$this->assertEquals(0.2, $this->task->getMembers()[$user2->getId()]['share']);
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
			$this->taskCreator->getId() => 0.4,
			$user1->getId()				=> 0.4,
			$user2->getId()				=> 0.2
		], $this->taskCreator);
		
		$this->task->assignShares([
			$this->taskCreator->getId() => 0.33,
			$user1->getId()				=> 0.18,
			$user2->getId()				=> 0.49
		], $user1);
		
		$this->task->assignShares([
			$this->taskCreator->getId() => 0.23,
			$user1->getId()				=> 0.54,
			$user2->getId()				=> 0.23
		], $user2);
		
		$this->assertEquals(0.32, $this->task->getMembers()[$this->taskCreator->getId()]['share']);
		$this->assertEquals(0.3733, $this->task->getMembers()[$user1->getId()]['share']);
		$this->assertEquals(0.3067, $this->task->getMembers()[$user2->getId()]['share']);
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
			$this->taskCreator->getId() => 0.4,
			$user1->getId()				=> 0.6,
			$user2->getId()				=> 0
		], $this->taskCreator);
		
		$this->task->assignShares([
			$this->taskCreator->getId() => 0.33,
			$user1->getId()				=> 0.67,
			$user2->getId()				=> 0
		], $user1);
		
		$this->task->assignShares([
			$this->taskCreator->getId() => 0.23,
			$user1->getId()				=> 0.77,
			$user2->getId()				=> 0
		], $user2);
		
		$this->assertEquals(0.32, $this->task->getMembers()[$this->taskCreator->getId()]['share']);
		$this->assertEquals(0.68, $this->task->getMembers()[$user1->getId()]['share']);
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
			$this->taskCreator->getId() => 0.4,
			$user1->getId()				=> 0.60,
			$user2->getId()				=> 0
		], $this->taskCreator);
		
		$this->task->assignShares([
			$this->taskCreator->getId() => 0.33,
			$user1->getId()				=> 0.43,
			$user2->getId()				=> 0.24
		], $user1);
		
		$this->task->assignShares([
			$this->taskCreator->getId() => 0.23,
			$user1->getId()				=> 0.77,
			$user2->getId()				=> 0
		], $user2);
		
		$this->assertEquals(0.32, $this->task->getMembers()[$this->taskCreator->getId()]['share']);
		$this->assertEquals(0.60, $this->task->getMembers()[$user1->getId()]['share']);
		$this->assertEquals(0.08, $this->task->getMembers()[$user2->getId()]['share']);
	}
	
	public function testOneMemberSkipSharesAssignment() {
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
				$this->taskCreator->getId() => 0.33,
				$user1->getId()				=> 0.39,
				$user2->getId()				=> 0.28
		], $user1);
	
		$this->task->assignShares([
				$this->taskCreator->getId() => 0.23,
				$user1->getId()				=> 0.77,
				$user2->getId()				=> 0
		], $user2);
	
		$this->assertEquals(0.28, $this->task->getMembers()[$this->taskCreator->getId()]['share']);
		$this->assertEquals(0.58, $this->task->getMembers()[$user1->getId()]['share']);
		$this->assertEquals(0.14, $this->task->getMembers()[$user2->getId()]['share']);
	}
	
	public function testAllMembersSkipSharesAssignment() {
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
		$this->task->skipShares($user1);
		$this->task->skipShares($user2);
		
		$this->assertArrayNotHasKey('share', $this->task->getMembers()[$this->taskCreator->getId()]);
		$this->assertArrayNotHasKey('share', $this->task->getMembers()[$user1->getId()]);
		$this->assertArrayNotHasKey('share', $this->task->getMembers()[$user2->getId()]);
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
				$this->taskCreator->getId() => 0.33,
				$user1->getId()				=> 0.39,
				$user2->getId()				=> 0.28
		], $user1);
		
		$this->task->assignShares([
				$this->taskCreator->getId() => 0.23,
				$user1->getId()				=> 0.77,
				$user2->getId()				=> 0
		], $user2);
		
		$this->assertEquals(Task::STATUS_CLOSED, $this->task->getStatus());
	}
	
	public function testGetMembersCredits() {
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
			$this->taskCreator->getId() => 0.4,
			$user1->getId()				=> 0.4,
			$user2->getId()				=> 0.2
		], $this->taskCreator);
		
		$this->task->assignShares([
			$this->taskCreator->getId() => 0.33,
			$user1->getId()				=> 0.18,
			$user2->getId()				=> 0.49
		], $user1);
		
		$this->task->assignShares([
			$this->taskCreator->getId() => 0.23,
			$user1->getId()				=> 0.54,
			$user2->getId()				=> 0.23
		], $user2);
		
		$this->assertEquals(714.67, $this->task->getMembersCredits()[$this->taskCreator->getId()]);
		$this->assertEquals(833.70, $this->task->getMembersCredits()[$user1->getId()]);
		$this->assertEquals(684.96, $this->task->getMembersCredits()[$user2->getId()]);
	}

	public function testGetMembersCreditsWhenEverybodySkip() {
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
		$this->task->skipShares($user1);
		$this->task->skipShares($user2);
		
		$this->assertEquals(0, $this->task->getMembersCredits()[$this->taskCreator->getId()]);
		$this->assertEquals(0, $this->task->getMembersCredits()[$user1->getId()]);
		$this->assertEquals(0, $this->task->getMembersCredits()[$user2->getId()]);
	}
}
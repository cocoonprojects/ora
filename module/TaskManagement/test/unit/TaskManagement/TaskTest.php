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
	 * 
	 * @var Stream
	 */
	protected $stream;

	
	protected function setUp() {
		$this->owner = $this->getMockBuilder(User::class)
			->getMock();
		$this->owner->method('getId')
			->willReturn('60000000-0000-0000-0000-000000000000');
		$this->owner->method('isMemberOf')
			->willReturn(true);
		$organization = Organization::create('Pellentesque lorem ligula, auctor ac', $this->owner);
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

		$member1 = $this->getMockBuilder(User::class)
			->getMock();
		$member1->method('getId')
			->willReturn('80000000-0000-0000-0000-000000000000');
		$member1->method('isMemberOf')
			->willReturn(true);
		$member2 = $this->getMockBuilder(User::class)
			->getMock();
		$member2->method('getId')
			->willReturn('90000000-0000-0000-0000-000000000000');
		$member2->method('isMemberOf')
			->willReturn(true);
		$task->addMember($member1, Task::ROLE_MEMBER);
		$task->addMember($member2);
		
		$members = $task->getMembers();
		$this->assertCount(3, $members);
		$this->assertArrayHasKey($this->owner->getId(), $members);
		$this->assertArrayHasKey($member1->getId(), $members);
		$this->assertArrayHasKey($member2->getId(), $members);
		$this->assertEquals(Task::ROLE_OWNER, $members[$this->owner->getId()]['role']);
		$this->assertEquals(Task::ROLE_MEMBER, $members[$member1->getId()]['role']);
		$this->assertEquals(Task::ROLE_MEMBER, $members[$member2->getId()]['role']);
		$this->assertArrayNotHasKey('accountId', $members[$this->owner->getId()]);
		$this->assertArrayNotHasKey('accountId', $members[$member1->getId()]);
		$this->assertArrayNotHasKey('accountId', $members[$member2->getId()]);
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
		
		$user1 = $this->getMockBuilder(User::class)
			->getMock();
		$user1->method('getId')
			->willReturn('80000000-0000-0000-0000-000000000000');
		$user1->method('isMemberOf')
			->willReturn(true);
		$user2 = $this->getMockBuilder(User::class)
			->getMock();
		$user2->method('getId')
			->willReturn('90000000-0000-0000-0000-000000000000');
		$user2->method('isMemberOf')
			->willReturn(true);
		
		$task->addMember($user1);
		$task->addMember($user2);
		
		$task->addEstimation(20, $user1);
		$task->addEstimation(1000, $user2);
		
		$members = $task->getMembers();
		
		$this->assertArrayHasKey($user1->getId(), $members);
		$this->assertArrayHasKey($user2->getId(), $members);
		$this->assertEquals(20, $members[$user1->getId()]['estimation']);
		$this->assertEquals(1000, $members[$user2->getId()]['estimation']);
	}
	
	public function testAssignShare() {
		$task = Task::create($this->stream, null, $this->owner);
		$task->addMember($this->owner, Task::ROLE_OWNER);

		$user1 = $this->getMockBuilder(User::class)
			->getMock();
		$user1->method('getId')
			->willReturn('80000000-0000-0000-0000-000000000000');
		$user1->method('isMemberOf')
			->willReturn(true);
		$user2 = $this->getMockBuilder(User::class)
			->getMock();
		$user2->method('getId')
			->willReturn('90000000-0000-0000-0000-000000000000');
		$user2->method('isMemberOf')
			->willReturn(true);
		
		$task->addMember($user1);
		$task->addMember($user2);
		
		$task->complete($this->owner);
		
		$task->addEstimation(1000, $user1);
		$task->addEstimation(2500, $user2);
		$task->addEstimation(3200, $this->owner);
		
		$task->accept($this->owner);
		
		$task->assignShares([
			$this->owner->getId() => 0.4,
			$user1->getId()       => 0.4,
			$user2->getId()       => 0.2
		], $this->owner);
		
		$this->assertEquals(0.4, $task->getMembers()[$this->owner->getId()]['share']);
		$this->assertEquals(0.4, $task->getMembers()[$user1->getId()]['share']);
		$this->assertEquals(0.2, $task->getMembers()[$user2->getId()]['share']);
	}
	
	public function testEveryMemberAssignShares() {
		$task = Task::create($this->stream, null, $this->owner);
		$task->addMember($this->owner, Task::ROLE_OWNER);

		$user1 = $this->getMockBuilder(User::class)
			->getMock();
		$user1->method('getId')
			->willReturn('80000000-0000-0000-0000-000000000000');
		$user1->method('isMemberOf')
			->willReturn(true);
		$user2 = $this->getMockBuilder(User::class)
			->getMock();
		$user2->method('getId')
			->willReturn('90000000-0000-0000-0000-000000000000');
		$user2->method('isMemberOf')
			->willReturn(true);
		
		$task->addMember($user1, $user1);
		$task->addMember($user2, $user2);
		
		$task->complete($this->owner);
		
		$task->addEstimation(1000, $user1);
		$task->addEstimation(2500, $user2);
		$task->addEstimation(3200, $this->owner);
		
		$task->accept($this->owner);
		
		$task->assignShares([
			$this->owner->getId() => 0.4,
			$user1->getId()				=> 0.4,
			$user2->getId()				=> 0.2
		], $this->owner);
		
		$task->assignShares([
			$this->owner->getId() => 0.33,
			$user1->getId()				=> 0.18,
			$user2->getId()				=> 0.49
		], $user1);
		
		$task->assignShares([
			$this->owner->getId() => 0.23,
			$user1->getId()				=> 0.54,
			$user2->getId()				=> 0.23
		], $user2);
		
		$this->assertEquals(0.32, $task->getMembers()[$this->owner->getId()]['share']);
		$this->assertEquals(0.3733, $task->getMembers()[$user1->getId()]['share']);
		$this->assertEquals(0.3067, $task->getMembers()[$user2->getId()]['share']);
	}

	public function testEveryMemberAssignSharesWithAMemberTo0() {
		$task = Task::create($this->stream, null, $this->owner);
		$task->addMember($this->owner, Task::ROLE_OWNER);

		$user1 = $this->getMockBuilder(User::class)
			->getMock();
		$user1->method('getId')
			->willReturn('80000000-0000-0000-0000-000000000000');
		$user1->method('isMemberOf')
			->willReturn(true);
		$user2 = $this->getMockBuilder(User::class)
			->getMock();
		$user2->method('getId')
			->willReturn('90000000-0000-0000-0000-000000000000');
		$user2->method('isMemberOf')
			->willReturn(true);
		
		$task->addMember($user1, $user1);
		$task->addMember($user2, $user2);
		
		$task->complete($this->owner);
		
		$task->addEstimation(1000, $user1);
		$task->addEstimation(2500, $user2);
		$task->addEstimation(3200, $this->owner);
		
		$task->accept($this->owner);
		
		$task->assignShares([
			$this->owner->getId() => 0.4,
			$user1->getId()				=> 0.6,
			$user2->getId()				=> 0
		], $this->owner);
		
		$task->assignShares([
			$this->owner->getId() => 0.33,
			$user1->getId()				=> 0.67,
			$user2->getId()				=> 0
		], $user1);
		
		$task->assignShares([
			$this->owner->getId() => 0.23,
			$user1->getId()				=> 0.77,
			$user2->getId()				=> 0
		], $user2);
		
		$this->assertEquals(0.32, $task->getMembers()[$this->owner->getId()]['share']);
		$this->assertEquals(0.68, $task->getMembers()[$user1->getId()]['share']);
		$this->assertEquals(0, $task->getMembers()[$user2->getId()]['share']);
	}

	public function testEveryMemberAssignSharesWith0() {
		$task = Task::create($this->stream, null, $this->owner);
		$task->addMember($this->owner, Task::ROLE_OWNER);

		$user1 = $this->getMockBuilder(User::class)
			->getMock();
		$user1->method('getId')
			->willReturn('80000000-0000-0000-0000-000000000000');
		$user1->method('isMemberOf')
			->willReturn(true);
		$user2 = $this->getMockBuilder(User::class)
			->getMock();
		$user2->method('getId')
			->willReturn('90000000-0000-0000-0000-000000000000');
		$user2->method('isMemberOf')
			->willReturn(true);
		
		$task->addMember($user1, $user1);
		$task->addMember($user2, $user2);
		
		$task->complete($this->owner);
		
		$task->addEstimation(1000, $user1);
		$task->addEstimation(2500, $user2);
		$task->addEstimation(3200, $this->owner);
		
		$task->accept($this->owner);
		
		$task->assignShares([
			$this->owner->getId() => 0.4,
			$user1->getId()				=> 0.60,
			$user2->getId()				=> 0
		], $this->owner);
		
		$task->assignShares([
			$this->owner->getId() => 0.33,
			$user1->getId()				=> 0.43,
			$user2->getId()				=> 0.24
		], $user1);
		
		$task->assignShares([
			$this->owner->getId() => 0.23,
			$user1->getId()				=> 0.77,
			$user2->getId()				=> 0
		], $user2);
		
		$this->assertEquals(0.32, $task->getMembers()[$this->owner->getId()]['share']);
		$this->assertEquals(0.60, $task->getMembers()[$user1->getId()]['share']);
		$this->assertEquals(0.08, $task->getMembers()[$user2->getId()]['share']);
	}
	
	public function testOneMemberSkipSharesAssignment() {
		$task = Task::create($this->stream, null, $this->owner);
		$task->addMember($this->owner, Task::ROLE_OWNER);

		$user1 = $this->getMockBuilder(User::class)
			->getMock();
		$user1->method('getId')
			->willReturn('80000000-0000-0000-0000-000000000000');
		$user1->method('isMemberOf')
			->willReturn(true);
		$user2 = $this->getMockBuilder(User::class)
			->getMock();
		$user2->method('getId')
			->willReturn('90000000-0000-0000-0000-000000000000');
		$user2->method('isMemberOf')
			->willReturn(true);
	
		$task->addMember($user1, $user1);
		$task->addMember($user2, $user2);
	
		$task->complete($this->owner);
	
		$task->addEstimation(1000, $user1);
		$task->addEstimation(2500, $user2);
		$task->addEstimation(3200, $this->owner);
	
		$task->accept($this->owner);
	
		$task->skipShares($this->owner);
	
		$task->assignShares([
				$this->owner->getId() => 0.33,
				$user1->getId()				=> 0.39,
				$user2->getId()				=> 0.28
		], $user1);
	
		$task->assignShares([
				$this->owner->getId() => 0.23,
				$user1->getId()				=> 0.77,
				$user2->getId()				=> 0
		], $user2);
	
		$this->assertEquals(0.28, $task->getMembers()[$this->owner->getId()]['share']);
		$this->assertEquals(0.58, $task->getMembers()[$user1->getId()]['share']);
		$this->assertEquals(0.14, $task->getMembers()[$user2->getId()]['share']);
	}
	
	public function testAllMembersSkipSharesAssignment() {
		$task = Task::create($this->stream, null, $this->owner);
		$task->addMember($this->owner, Task::ROLE_OWNER);

		$user1 = $this->getMockBuilder(User::class)
			->getMock();
		$user1->method('getId')
			->willReturn('80000000-0000-0000-0000-000000000000');
		$user1->method('isMemberOf')
			->willReturn(true);
		$user2 = $this->getMockBuilder(User::class)
			->getMock();
		$user2->method('getId')
			->willReturn('90000000-0000-0000-0000-000000000000');
		$user2->method('isMemberOf')
			->willReturn(true);
		
		$task->addMember($user1, $user1);
		$task->addMember($user2, $user2);
		
		$task->complete($this->owner);
		
		$task->addEstimation(1000, $user1);
		$task->addEstimation(2500, $user2);
		$task->addEstimation(3200, $this->owner);
		
		$task->accept($this->owner);
		
		$task->skipShares($this->owner);
		$task->skipShares($user1);
		$task->skipShares($user2);
		
		$this->assertArrayNotHasKey('share', $task->getMembers()[$this->owner->getId()]);
		$this->assertArrayNotHasKey('share', $task->getMembers()[$user1->getId()]);
		$this->assertArrayNotHasKey('share', $task->getMembers()[$user2->getId()]);
	}
	
	public function testLastUserShareAssignement() {
		$task = Task::create($this->stream, null, $this->owner);
		$task->addMember($this->owner, Task::ROLE_OWNER);

		$user1 = $this->getMockBuilder(User::class)
			->getMock();
		$user1->method('getId')
			->willReturn('80000000-0000-0000-0000-000000000000');
		$user1->method('isMemberOf')
			->willReturn(true);
		$user2 = $this->getMockBuilder(User::class)
			->getMock();
		$user2->method('getId')
			->willReturn('90000000-0000-0000-0000-000000000000');
		$user2->method('isMemberOf')
			->willReturn(true);
		
		$task->addMember($user1, $user1);
		$task->addMember($user2, $user2);
		
		$task->complete($this->owner);
		
		$task->addEstimation(1000, $user1);
		$task->addEstimation(2500, $user2);
		$task->addEstimation(3200, $this->owner);
		
		$task->accept($this->owner);
		
		$task->skipShares($this->owner);
		
		$task->assignShares([
				$this->owner->getId() => 0.33,
				$user1->getId()				=> 0.39,
				$user2->getId()				=> 0.28
		], $user1);
		
		$task->assignShares([
				$this->owner->getId() => 0.23,
				$user1->getId()				=> 0.77,
				$user2->getId()				=> 0
		], $user2);
		
		$this->assertTrue($task->isSharesAssignmentCompleted());
	}
	
	public function testGetMembersCredits() {
		$task = Task::create($this->stream, null, $this->owner);
		$task->addMember($this->owner, Task::ROLE_OWNER);

		$user1 = $this->getMockBuilder(User::class)
			->getMock();
		$user1->method('getId')
			->willReturn('80000000-0000-0000-0000-000000000000');
		$user1->method('isMemberOf')
			->willReturn(true);
		$user2 = $this->getMockBuilder(User::class)
			->getMock();
		$user2->method('getId')
			->willReturn('90000000-0000-0000-0000-000000000000');
		$user2->method('isMemberOf')
			->willReturn(true);
		
		$task->addMember($user1, $user1);
		$task->addMember($user2, $user2);
		
		$task->complete($this->owner);
		
		$task->addEstimation(1000, $user1);
		$task->addEstimation(2500, $user2);
		$task->addEstimation(3200, $this->owner);
		
		$task->accept($this->owner);
		
		$task->assignShares([
			$this->owner->getId() => 0.4,
			$user1->getId()				=> 0.4,
			$user2->getId()				=> 0.2
		], $this->owner);
		
		$task->assignShares([
			$this->owner->getId() => 0.33,
			$user1->getId()				=> 0.18,
			$user2->getId()				=> 0.49
		], $user1);
		
		$task->assignShares([
			$this->owner->getId() => 0.23,
			$user1->getId()				=> 0.54,
			$user2->getId()				=> 0.23
		], $user2);
		
		$this->assertEquals(714.67, $task->getMembersCredits()[$this->owner->getId()]);
		$this->assertEquals(833.70, $task->getMembersCredits()[$user1->getId()]);
		$this->assertEquals(684.96, $task->getMembersCredits()[$user2->getId()]);
	}

	public function testGetMembersCreditsWhenEverybodySkip() {
		$task = Task::create($this->stream, null, $this->owner);
		$task->addMember($this->owner, Task::ROLE_OWNER);

		$user1 = $this->getMockBuilder(User::class)
			->getMock();
		$user1->method('getId')
			->willReturn('80000000-0000-0000-0000-000000000000');
		$user1->method('isMemberOf')
			->willReturn(true);
		$user2 = $this->getMockBuilder(User::class)
			->getMock();
		$user2->method('getId')
			->willReturn('90000000-0000-0000-0000-000000000000');
		$user2->method('isMemberOf')
			->willReturn(true);
		
		$task->addMember($user1, $user1);
		$task->addMember($user2, $user2);
		
		$task->complete($this->owner);
		
		$task->addEstimation(1000, $user1);
		$task->addEstimation(2500, $user2);
		$task->addEstimation(3200, $this->owner);
		
		$task->accept($this->owner);
		
		$task->skipShares($this->owner);
		$task->skipShares($user1);
		$task->skipShares($user2);
		
		$this->assertEquals(0, $task->getMembersCredits()[$this->owner->getId()]);
		$this->assertEquals(0, $task->getMembersCredits()[$user1->getId()]);
		$this->assertEquals(0, $task->getMembersCredits()[$user2->getId()]);
	}
	
	public function testClose() {
		$task = Task::create($this->stream, null, $this->owner);
		$task->addMember($this->owner, Task::ROLE_OWNER);
		$task->addEstimation(1, $this->owner);
		$task->complete($this->owner);
		$task->accept($this->owner);
		$task->close($this->owner);
		$this->assertEquals(Task::STATUS_CLOSED, $task->getStatus());
	}
}
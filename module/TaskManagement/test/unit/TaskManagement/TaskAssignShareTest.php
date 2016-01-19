<?php

namespace TaskManagement;


use Application\Entity\User;
use People\Organization;

class TaskAssignShareTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * @var User
	 */
	private $owner;
	/**
	 * @var User
	 */
	private $user1;
	/**
	 * @var User
	 */
	private $user2;
	/**
	 * @var Task
	 */
	private $task;
	/**
	 * Sets up the fixture, for example, open a network connection.
	 * This method is called before a test is executed.
	 */
	protected function setUp()
	{
		$this->owner = User::create();
		$this->user1 = User::create();
		$this->user2 = User::create();

		$organization = Organization::create('Pellentesque lorem ligula, auctor ac', $this->owner);
		$this->owner->addMembership($organization);
		$organization->addMember($this->user1);
		$this->user1->addMembership($organization);
		$organization->addMember($this->user2);
		$this->user2->addMembership($organization);


		$stream = Stream::create($organization, 'Curabitur rhoncus mattis massa vel', $this->owner);
		$this->task = Task::create($stream, null, $this->owner);
		$this->task->addMember($this->owner, Task::ROLE_OWNER);
		$this->task->addMember($this->user1);
		$this->task->addMember($this->user2);
		$this->task->execute($this->owner);
		$this->task->addEstimation(1000, $this->user1);
		$this->task->addEstimation(2500, $this->user2);
		$this->task->addEstimation(3200, $this->owner);
		$this->task->complete($this->owner);
		$this->task->accept($this->owner, new \DateInterval('P7D'));
	}

	public function testAssignShare()
	{
		$this->task->assignShares([
			$this->owner->getId() => 0.4,
			$this->user1->getId() => 0.4,
			$this->user2->getId() => 0.2
		], $this->owner);

		$this->assertEquals(0.4, $this->task->getMembers()[$this->owner->getId()]['share']);
		$this->assertEquals(0.4, $this->task->getMembers()[$this->user1->getId()]['share']);
		$this->assertEquals(0.2, $this->task->getMembers()[$this->user2->getId()]['share']);
	}

	public function testEveryMemberAssignShares()
	{
		$this->task->assignShares([
			$this->owner->getId() => 0.4,
			$this->user1->getId() => 0.4,
			$this->user2->getId() => 0.2
		], $this->owner);

		$this->task->assignShares([
			$this->owner->getId() => 0.33,
			$this->user1->getId() => 0.18,
			$this->user2->getId() => 0.49
		], $this->user1);

		$this->task->assignShares([
			$this->owner->getId() => 0.23,
			$this->user1->getId() => 0.54,
			$this->user2->getId() => 0.23
		], $this->user2);

		$this->assertEquals(0.32, $this->task->getMembers()[$this->owner->getId()]['share']);
		$this->assertEquals(0.3733, $this->task->getMembers()[$this->user1->getId()]['share']);
		$this->assertEquals(0.3067, $this->task->getMembers()[$this->user2->getId()]['share']);
		$this->assertEquals(714.67, $this->task->getMembersCredits()[$this->owner->getId()]);
		$this->assertEquals(833.70, $this->task->getMembersCredits()[$this->user1->getId()]);
		$this->assertEquals(684.96, $this->task->getMembersCredits()[$this->user2->getId()]);
	}

	public function testEveryMemberAssign0SharesToAMember() {
		$this->task->assignShares([
			$this->owner->getId() => 0.4,
			$this->user1->getId() => 0.6,
			$this->user2->getId() => 0
		], $this->owner);

		$this->task->assignShares([
			$this->owner->getId() => 0.33,
			$this->user1->getId() => 0.67,
			$this->user2->getId() => 0
		], $this->user1);

		$this->task->assignShares([
			$this->owner->getId() => 0.23,
			$this->user1->getId() => 0.77,
			$this->user2->getId() => 0
		], $this->user2);

		$this->assertEquals(0.32, $this->task->getMembers()[$this->owner->getId()]['share']);
		$this->assertEquals(0.68, $this->task->getMembers()[$this->user1->getId()]['share']);
		$this->assertEquals(0, $this->task->getMembers()[$this->user2->getId()]['share']);
	}

	public function testEveryMemberAssignSharesWith0() {
		$this->task->assignShares([
			$this->owner->getId() => 0.4,
			$this->user1->getId() => 0.60,
			$this->user2->getId() => 0
		], $this->owner);

		$this->task->assignShares([
			$this->owner->getId() => 0.33,
			$this->user1->getId() => 0.43,
			$this->user2->getId() => 0.24
		], $this->user1);

		$this->task->assignShares([
			$this->owner->getId() => 0.23,
			$this->user1->getId() => 0.77,
			$this->user2->getId() => 0
		], $this->user2);

		$this->assertEquals(0.32, $this->task->getMembers()[$this->owner->getId()]['share']);
		$this->assertEquals(0.60, $this->task->getMembers()[$this->user1->getId()]['share']);
		$this->assertEquals(0.08, $this->task->getMembers()[$this->user2->getId()]['share']);
	}

	public function testOneMemberSkipSharesAssignment() {
		$this->task->skipShares($this->owner);

		$this->task->assignShares([
			$this->owner->getId() => 0.33,
			$this->user1->getId() => 0.39,
			$this->user2->getId() => 0.28
		], $this->user1);

		$this->task->assignShares([
			$this->owner->getId() => 0.23,
			$this->user1->getId() => 0.77,
			$this->user2->getId() => 0
		], $this->user2);

		$this->assertEquals(0.28, $this->task->getMembers()[$this->owner->getId()]['share']);
		$this->assertEquals(0.58, $this->task->getMembers()[$this->user1->getId()]['share']);
		$this->assertEquals(0.14, $this->task->getMembers()[$this->user2->getId()]['share']);
	}

	public function testAllMembersSkipSharesAssignment() {
		$this->task->skipShares($this->owner);
		$this->task->skipShares($this->user1);
		$this->task->skipShares($this->user2);

		$this->assertArrayNotHasKey('share', $this->task->getMembers()[$this->owner->getId()]);
		$this->assertArrayNotHasKey('share', $this->task->getMembers()[$this->user1->getId()]);
		$this->assertArrayNotHasKey('share', $this->task->getMembers()[$this->user2->getId()]);
	}

	public function testLastUserShareAssignement() {
		$this->task->skipShares($this->owner);

		$this->task->assignShares([
			$this->owner->getId() => 0.33,
			$this->user1->getId() => 0.39,
			$this->user2->getId() => 0.28
		], $this->user1);

		$this->task->assignShares([
			$this->owner->getId() => 0.23,
			$this->user1->getId() => 0.77,
			$this->user2->getId() => 0
		], $this->user2);

		$this->assertTrue($this->task->isSharesAssignmentCompleted());
	}

	public function testGetMembersCreditsWhenEverybodySkip() {
		$this->task->skipShares($this->owner);
		$this->task->skipShares($this->user1);
		$this->task->skipShares($this->user2);

		$this->assertEquals(0, $this->task->getMembersCredits()[$this->owner->getId()]);
		$this->assertEquals(0, $this->task->getMembersCredits()[$this->user1->getId()]);
		$this->assertEquals(0, $this->task->getMembersCredits()[$this->user2->getId()]);
	}
}
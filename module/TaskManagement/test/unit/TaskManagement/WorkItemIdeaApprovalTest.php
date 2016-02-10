<?php

namespace TaskManagement;


use Application\Entity\User;
use People\Organization;

class WorkItemIdeaApprovalTest extends \PHPUnit_Framework_TestCase
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
		$this->task->open($this->owner);
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

	
}
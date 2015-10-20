<?php 

namespace TaskManagement\Service;

use Application\Entity\User;
use People\Entity\Organization;
use TaskManagement\Entity\Estimation;
use TaskManagement\Entity\Task;
use TaskManagement\Entity\Stream;

class TaskCommandListenerTest extends \PHPUnit_Framework_TestCase{
	
	/**
	 * @var TaskCommandsListener
	 */
	protected $listener;
	/**
	 * @var Task
	 */
	protected $task;
	/**
	 * @var TaskMember
	 */
	protected $taskOwner;
	/**
	 * @var TaskMember
	 */
	protected $taskMember;
	
	public function setUp(){
		parent::setUp();
		$entityManager = $this->getMock('\Doctrine\ORM\EntityManager', array('getRepository', 'getClassMetadata', 'persist', 'flush'), array(), '', false);
		$this->listener = new TaskCommandsListener($entityManager);
		$this->task = new Task("0011", new Stream("001", new Organization("00")));
		$owner = User::create();
		$member = User::create();
		$this->task->addMember($owner, Task::ROLE_OWNER, $owner, new \DateTime());
		$this->task->addMember($member, Task::ROLE_MEMBER, $member, new \DateTime());
		$this->taskOwner = $this->task->getMember($owner);
		$this->taskMember = $this->task->getMember($member);
	}

	public function testGetMembersCredits(){

		$this->taskOwner->setEstimation(new Estimation(1000, new \DateTime()))
			->assignShare($this->taskMember, 0.5, new \DateTime())
			->assignShare($this->taskOwner, 0.5, new \DateTime());
		$this->taskMember->setEstimation(new Estimation(2000, new \DateTime()))
			->assignShare($this->taskOwner, 0.5, new \DateTime())
			->assignShare($this->taskMember, 0.5, new \DateTime());
		$this->listener->setMemberCredits($this->task);

		$this->assertEquals(750, $this->taskOwner->getCredits());
		$this->assertEquals(750, $this->taskMember->getCredits());
	}

	public function testGetMembersCreditsWhenEverybodySkipEstimation(){

		$this->taskOwner->setEstimation(new Estimation(Estimation::NOT_ESTIMATED, new \DateTime()))
			->assignShare($this->taskMember, 0.5, new \DateTime())
			->assignShare($this->taskOwner, 0.5, new \DateTime());
		$this->taskMember->setEstimation(new Estimation(Estimation::NOT_ESTIMATED, new \DateTime()))
			->assignShare($this->taskOwner, 0.5, new \DateTime())
			->assignShare($this->taskMember, 0.5, new \DateTime());
		$this->listener->setMemberCredits($this->task);

		$this->assertEquals(0, $this->taskOwner->getCredits());
		$this->assertEquals(0, $this->taskMember->getCredits());
	}

	public function testGetMembersCreditsWhenEverybodySkipShares(){

		$this->taskOwner->setEstimation(new Estimation(1000, new \DateTime()))
			->assignShare($this->taskMember, null, new \DateTime())
			->assignShare($this->taskOwner, null, new \DateTime());
		$this->taskMember->setEstimation(new Estimation(2000, new \DateTime()))
			->assignShare($this->taskOwner, null, new \DateTime())
			->assignShare($this->taskMember, null, new \DateTime());
		$this->listener->setMemberCredits($this->task);

		$this->assertEquals(0, $this->taskOwner->getCredits());
		$this->assertEquals(0, $this->taskMember->getCredits());
	}
}
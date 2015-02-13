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
	 * @var Organization
	 */
	protected $organization;
	
	
	protected function setUp() {
		$this->taskCreator = User::create();
		$this->organization = new ReadModelOrganization(Uuid::fromString('00000000-1000-0000-0000-000000000022'), new \DateTime(), $this->taskCreator);		
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
	
	public function testGetMembersShare() {
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
	
	public function testAssignShares() {
		
	}
}
<?php
namespace Ora\TaskManagement;

use Ora\StreamManagement\Stream;
use Rhumsaa\Uuid\Uuid;
use Ora\User\User;
class TaskTest extends \PHPUnit_Framework_TestCase {
	
	/**
	 * 
	 * @var Task
	 */
	protected $task;
	
	protected function setUp() {
		$user = User::create();
		$stream = new Stream(Uuid::fromString('00000000-1000-0000-0000-000000000002'), $user);
		$this->task = Task::create($stream, 'test', $user);
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
}
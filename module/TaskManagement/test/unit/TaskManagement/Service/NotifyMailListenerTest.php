<?php
namespace TaskManagement\Service;

use AcMailer\Result\MailResult;
use AcMailer\Service\MailServiceInterface;
use Application\Entity\User;
use Application\Service\UserService;
use People\Entity\Organization as ReadModelOrganization;
use People\Organization;
use TaskManagement\Entity\Stream as ReadModelStream;
use TaskManagement\Entity\Task as ReadModelTask;
use TaskManagement\Entity\TaskMember;
use TaskManagement\Stream;
use TaskManagement\Task;
use Zend\Mail\Message;


class NotifyMailListenerTest extends \PHPUnit_Framework_TestCase {

	/**
	 * @var NotifyMailListener
	 */
	protected $listener;
	/**
	 * @var Task
	 */
	protected $task;
	/**
	 * @var User
	 */
	protected $owner;
	/**
	 * @var User
	 */
	protected $member;
	/**
	 * @var ReadModelTask
	 */
	protected $readModelTask;

	protected function setUp(){
		$this->owner = User::create();
		$this->owner->setFirstname('John');
		$this->owner->setLastname('Doe');
		$this->owner->setEmail('john.doe@foo.com');

		$organization = Organization::create('Organization_test', $this->owner);

		$this->owner->addMembership($organization);

		//Task Member
		$this->member = User::create();
		$this->member->setFirstname('Jane');
		$this->member->setLastname('Doe');
		$this->member->setEmail('jane.doe@foo.com');
		$this->member->addMembership($organization);

		//Organization & Stream for Task Creation
		$stream = Stream::create($organization, 'Steram_test', $this->owner);
		
		$this->task = Task::create($stream, 'Lorem Ipsum Sic Dolor Amit', $this->owner);
		$this->task->addMember($this->owner, Task::ROLE_OWNER);
		$this->task->addMember($this->member);
		
		$mailService = $this->getMockBuilder(MailServiceInterface::class)->getMock();
		$mailService->expects($this->once())
			->method('send')
			->willReturn(new MailResult(true));
		$mailService->expects($this->once())
			->method('getMessage')
			->willReturn(new Message());

		$userService = $this->getMockBuilder(UserService::class)->getMock();
		$userService->method('findUser')->willReturn($this->owner);

		$taskService = $this->getMockBuilder(TaskService::class)->getMock();

		$this->listener = new NotifyMailListener($mailService, $userService, $taskService);

		$this->readModelTask = new ReadModelTask($this->task->getId());
		$this->readModelTask->setSubject($this->task->getSubject());
		$this->readModelTask->addMember($this->member, TaskMember::ROLE_MEMBER, $this->member, new \DateTime());

		$s = new ReadModelStream($stream->getId());
		$organization = new ReadModelOrganization($organization);
		$s->setOrganization($organization);
		$this->readModelTask->setStream($s);
	}

	public function testSendEstimationAddedInfoMail() {
		$this->listener->getMailService()
			->expects($this->once())
			->method('setTemplate')
			->with('mail/estimation-added-info.phtml', [
				'task' => $this->task,
				'recipient' => $this->owner,
				'member' => $this->member
			]);
		$this->listener->sendEstimationAddedInfoMail($this->task, $this->member);
	}
	
	public function testSendSharesAssignedInfoMail() {
		$this->listener->getMailService()
			->expects($this->once())
			->method('setTemplate')
			->with('mail/shares-assigned-info.phtml', [
				'task' => $this->task,
				'recipient' => $this->owner,
				'member' => $this->member
			]);
		$this->listener->sendSharesAssignedInfoMail($this->task, $this->member);
	}
	
	public function testRemindAssignmentOfShares() {
		$this->listener->getMailService()
			->expects($this->once())
			->method('setTemplate')
			->with('mail/reminder-assignment-shares.phtml', [
				'task' => $this->readModelTask,
				'recipient'=> $this->member
			]);
		$this->listener->remindAssignmentOfShares($this->readModelTask);
	}

	public function testReminderAddEstimation() {
		$this->listener->getMailService()
			->expects($this->once())
			->method('setTemplate')
			->with('mail/reminder-add-estimation.phtml', [
				'task' => $this->readModelTask,
				'recipient'=> $this->member
			]);
		$this->listener->reminderAddEstimation($this->readModelTask);
	}

	public function testSendTaskClosedInfoMail() {
		$this->listener->getMailService()
			->expects($this->once())
			->method('setTemplate')
			->with('mail/task-closed-info.phtml', [
				'task' => $this->readModelTask,
				'recipient'=> $this->member
			]);
		$this->listener->sendTaskClosedInfoMail($this->readModelTask);
	}
}

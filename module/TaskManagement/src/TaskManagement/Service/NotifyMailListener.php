<?php

namespace TaskManagement\Service;

use AcMailer\Service\MailServiceInterface;
use Application\Entity\BasicUser;
use Application\Entity\User;
use Application\Service\UserService;
use People\Entity\OrganizationMembership;
use People\Service\OrganizationService;
use TaskManagement\Entity\Task;
use TaskManagement\EstimationAdded;
use TaskManagement\SharesAssigned;
use TaskManagement\SharesSkipped;
use TaskManagement\TaskClosed;
use TaskManagement\TaskCreated;
use TaskManagement\WorkItemIdeaCreated;
use Zend\EventManager\Event;
use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\ListenerAggregateInterface;
use Zend\Mvc\Application;

class NotifyMailListener implements NotificationService, ListenerAggregateInterface
{
	/**
	 * @var MailServiceInterface
	 */
	private $mailService;
	/**
	 * @var UserService
	 */
	private $userService;
	/**
	 * @var TaskService
	 */
	private $taskService;
	/**
	 * @var OrganizationService
	 */
	private $orgService;

	
	protected $listeners = [];
	
	public function __construct(MailServiceInterface $mailService, UserService $userService, TaskService $taskService, OrganizationService $orgService) {
		$this->mailService = $mailService;
		$this->userService = $userService;
		$this->taskService = $taskService;
		$this->orgService = $orgService;
	
	}
	
	public function attach(EventManagerInterface $events) {
		$this->listeners [] = $events->getSharedManager ()->attach (Application::class, EstimationAdded::class, array($this, 'processEstimationAdded'));
		$this->listeners [] = $events->getSharedManager ()->attach (Application::class, SharesAssigned::class, array($this, 'processSharesAssigned'));
		$this->listeners [] = $events->getSharedManager ()->attach (Application::class, SharesSkipped::class, array($this, 'processSharesAssigned'));
		$this->listeners [] = $events->getSharedManager ()->attach (Application::class, TaskClosed::class, array($this, 'processTaskClosed'));
		$this->listeners [] = $events->getSharedManager ()->attach (Application::class, TaskCreated::class, array($this, 'processWorkItemIdeaCreated'));
	}
	
	public function detach(EventManagerInterface $events) {
		foreach ( $this->listeners as $index => $listener ) {
			if ($events->detach ( $listener )) {
				unset ( $this->listeners [$index] );
			}
		}
	}

	public function processEstimationAdded(Event $event) {
		$streamEvent = $event->getTarget();
		$taskId = $streamEvent->metadata()['aggregate_id'];
		$task = $this->taskService->findTask($taskId);
		$memberId = $event->getParam ( 'by' );
		$member = $this->userService->findUser($memberId);
		$this->sendEstimationAddedInfoMail($task, $member);
	}

	public function processSharesAssigned(Event $event) {
		$streamEvent = $event->getTarget();
		$taskId = $streamEvent->metadata()['aggregate_id'];
		$task = $this->taskService->findTask($taskId);
		$memberId = $event->getParam ( 'by' );
		$member = $this->userService->findUser($memberId);
		$this->sendSharesAssignedInfoMail($task, $member);
	}

	public function processTaskClosed(Event $event){
		$streamEvent = $event->getTarget();
		$taskId = $streamEvent->metadata()['aggregate_id'];
		$task = $this->taskService->findTask($taskId);
		$this->sendTaskClosedInfoMail($task);
	}
	
	public function processWorkItemIdeaCreated(Event $event) {
		$streamEvent = $event->getTarget ();
		$taskId = $streamEvent->metadata ()['aggregate_id'];
		$task = $this->taskService->findTask ( $taskId );
		if ($task->getStatus() == Task::STATUS_IDEA) {
			$memberId = $event->getParam ( 'by' );
			$member = $task->getMember($memberId)->getUser();
			$org = $task->getStream()->getOrganization();
			$memberships = $this->orgService->findOrganizationMemberships($org,null,null);
			$this->sendWorkItemIdeaCreatedMail ( $task, $member, $memberships);
		}
	}

	/**
	 * @param Task $task
	 * @param User $member
	 * @return BasicUser[] receivers
	 * @throws \AcMailer\Exception\MailException
	 */
	public function sendEstimationAddedInfoMail(Task $task, User $member)
	{
		$rv = [];
		$owner = $task->getOwner()->getUser();

		//No mail to Owner for his actions
		if(strcmp($owner->getId(), $member->getId())==0){
			return $rv;
		}
		
		$message = $this->mailService->getMessage();
		$message->setTo($owner->getEmail());
		$message->setSubject ( 'Estimation added to "' . $task->getSubject() . '"');
		
		$this->mailService->setTemplate( 'mail/estimation-added-info.phtml', [
				'task' => $task,
				'recipient'=> $owner,
				'member'=> $member
		]);
		
		$this->mailService->send();
		$rv[] = $owner;
		return $rv;
	}

	/**
	 * @param Task $task
	 * @param User $member
	 * @return BasicUser[] receivers
	 * @throws \AcMailer\Exception\MailException
	 */
	public function sendSharesAssignedInfoMail(Task $task, User $member)
	{
		$rv = [];
		$owner = $task->getOwner()->getUser();

		//No mail to Owner for his actions
		if(strcmp($owner->getId(), $member->getId())==0){
			return $rv;
		}

		$message = $this->mailService->getMessage();
		$message->setTo($owner->getEmail());
		$message->setSubject ( 'Shares assigned to "' . $task->getSubject() . '"' );

		$this->mailService->setTemplate( 'mail/shares-assigned-info.phtml', [
			'task' => $task,
			'recipient'=> $owner,
			'member'=> $member
		]);

		$this->mailService->send();
		$rv[] = $owner;
		return $rv;
	}
	
	/**
	 * Send email notification to all members with empty shares of $taskToNotify
	 * @param Task $task
	 * @return BasicUser[] receivers
	 */
	public function remindAssignmentOfShares(Task $task)
	{
		$rv = [];
		$taskMembersWithEmptyShares = $task->findMembersWithEmptyShares();
		foreach ($taskMembersWithEmptyShares as $tm){
			$member = $tm->getUser();
			$message = $this->mailService->getMessage();
			$message->setTo($member->getEmail());
			$message->setSubject('Assign your shares to "' . $task->getSubject() . '"');

			$this->mailService->setTemplate( 'mail/reminder-assignment-shares.phtml', [
					'task' => $task,
					'recipient'=> $member
			]);

			$this->mailService->send();
			$rv[] = $member;
		}
		return $rv;
	}
	
	/**
	 * Send email notification to all members with no estimation of $taskToNotify
	 * @param Task $task
	 * @return BasicUser[] receivers
	 */
	public function remindEstimation(Task $task)
	{
		$rv = [];
		$taskMembersWithNoEstimation = $task->findMembersWithNoEstimation();
		foreach ($taskMembersWithNoEstimation as $tm){
			$member = $tm->getUser();
			$message = $this->mailService->getMessage();
			$message->setTo($member->getEmail());
			$message->setSubject("Estimate " . $task->getSubject());
			
			$this->mailService->setTemplate( 'mail/reminder-add-estimation.phtml', [
				'task' => $task,
				'recipient'=> $member
			]);
			
			$this->mailService->send();
			$rv[] = $member;
		}
		return $rv;
	}
	
	/**
	 * Send an email notification to the members of $taskToNotify to inform them that it has been closed
	 * @param Task $task
	 * @return BasicUser[] receivers
	 */
	public function sendTaskClosedInfoMail(Task $task)
	{
		$rv = [];
		$taskMembers = $task->getMembers();
		foreach ($taskMembers as $taskMember) {
			$member = $taskMember->getMember();
	
			$message = $this->mailService->getMessage();
			$message->setTo($member->getEmail());
			$message->setSubject($task->getSubject() . " closed");
	
			$this->mailService->setTemplate( 'mail/task-closed-info.phtml', [
				'task' => $task,
				'recipient'=> $member
			]);
			
			$this->mailService->send();
			$rv[] = $member;
		}
		return $rv;
	}
	
	/**
	 * Send an email notification to the organization members to inform them that a new Work Item Idea has been created
	 * @param Task $task
	 * @param User $member
	 * @param OrganizationMembership[] $memberships
	 * @return BasicUser[] receivers
	 */
	public function sendWorkItemIdeaCreatedMail(Task $task, User $member, $memberships){
		$rv = [];
		$org = $task->getStream()->getOrganization();
		$stream = $task->getStream();
		
		foreach ($memberships as $m) {
			$recipient = $m->getMember();
			
			$message = $this->mailService->getMessage();
			$message->setTo($recipient->getEmail());
			$message->setSubject("A new Work Item Idea has been proposed.");
			
			$this->mailService->setTemplate( 'mail/work-item-idea-created.phtml', [
					'task' => $task,
					'member' =>$member,
					'recipient'=> $recipient,
					'organization'=> $org,
					'stream'=> $stream
			]);
			$this->mailService->send();
			$rv[] = $recipient;
		}
		return $rv;
	}

	/**
	 * @return MailServiceInterface
	 */
	public function getMailService() {
		return $this->mailService;
	}

	public function getOrganizationService(){
		return $this->orgService;
	}
}

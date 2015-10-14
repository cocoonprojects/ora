<?php

namespace TaskManagement\Service;

use AcMailer\Service\MailServiceInterface;
use Application\Entity\User;
use Application\Service\UserService;
use TaskManagement\Entity\Task;
use TaskManagement\EstimationAdded;
use TaskManagement\SharesAssigned;
use TaskManagement\SharesSkipped;
use TaskManagement\TaskClosed;
use Zend\EventManager\Event;
use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\ListenerAggregateInterface;
use Zend\Mvc\Application;
use TaskManagement\WorkItemIdeaCreated;
use People\Service\OrganizationService;
use People\Entity\Organization;
use TaskManagement\Stream;
use TaskManagement\TaskCreated;
use People\Entity\OrganizationMembership;

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
	 * @return bool
	 * @throws \AcMailer\Exception\MailException
	 */
	public function sendEstimationAddedInfoMail(Task $task, User $member)
	{
		$owner = $task->getOwner()->getUser();

		//No mail to Owner for his actions
		if(strcmp($owner->getId(), $member->getId())==0){
			return;
		}
		
		$message = $this->mailService->getMessage();
		$message->setTo($owner->getEmail());
		$message->setSubject ( 'A member just estimated "' . $task->getSubject() . '"');
		
		$this->mailService->setTemplate( 'mail/estimation-added-info.phtml', [
				'task' => $task,
				'recipient'=> $owner,
				'member'=> $member
		]);
		
		$result = $this->mailService->send();
		return $result->isValid();
	}

	/**
	 * @param Task $task
	 * @param User $member
	 * @return bool|void
	 * @throws \AcMailer\Exception\MailException
	 */
	public function sendSharesAssignedInfoMail(Task $task, User $member)
	{
		$owner = $task->getOwner()->getUser();

		//No mail to Owner for his actions
		if(strcmp($owner->getId(), $member->getId())==0){
			return;
		}

		$message = $this->mailService->getMessage();
		$message->setTo($owner->getEmail());
		$message->setSubject ( 'A member just assigned its shares to "' . $task->getSubject() . '"' );

		$this->mailService->setTemplate( 'mail/shares-assigned-info.phtml', [
			'task' => $task,
			'recipient'=> $owner,
			'member'=> $member
		]);

		$result = $this->mailService->send();
		return $result->isValid();
	}
	
	/**
	 * Send email notification to all members with empty shares of $taskToNotify
	 * @param Task $task
	 */
	public function remindAssignmentOfShares(Task $task)
	{
		$taskMembersWithEmptyShares = $task->findMembersWithEmptyShares();
		foreach ($taskMembersWithEmptyShares as $tm){
			$member = $tm->getUser();
			$message = $this->mailService->getMessage();
			$message->setTo($member->getEmail());
			$message->setSubject("Assign your shares to " . $task->getSubject());

			$this->mailService->setTemplate( 'mail/reminder-assignment-shares.phtml', [
					'task' => $task,
					'recipient'=> $member
			]);
				
			$this->mailService->send();
		}
	}
	
	/**
	 * Send email notification to all members with no estimation of $taskToNotify
	 * @param Task $task
	 */
	public function remindEstimation(Task $task)
	{
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
		}
	}
	
	/**
	 * Send an email notification to the members of $taskToNotify to inform them that it has been closed
	 * @param Task $task
	 */
	public function sendTaskClosedInfoMail(Task $task)
	{
		$taskMembers = $task->getMembers();
		foreach ($taskMembers as $taskMember){
			
			$member = $taskMember->getMember();
	
			$message = $this->mailService->getMessage();
			$message->setTo($member->getEmail());
			$message->setSubject($task->getSubject() . " closed");
	
			$this->mailService->setTemplate( 'mail/task-closed-info.phtml', [
				'task' => $task,
				'recipient'=> $member
			]);
			
			$this->mailService->send();
		}
	}
	
	/**
	 * Send an email notification to the organization members to inform them that a new Work Item Idea has been created
	 * @param Task $task
	 * @param User $member
	 * @param OrganizationMembership[] $memberships
	 */
	public function sendWorkItemIdeaCreatedMail(Task $task, User $member, $memberships){
		$org = $task->getStream()->getOrganization();
		$stream = $task->getStream();
		
		foreach ($memberships as $m){
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
		}
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
	
	public function getStreamService(){
		return $this->streamService;
	}
}

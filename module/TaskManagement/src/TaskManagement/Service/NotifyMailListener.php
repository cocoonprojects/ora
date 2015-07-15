<?php

namespace TaskManagement\Service;

use AcMailer\Service\MailService;
use Application\Service\UserService;
use Application\Entity\User;
use TaskManagement\EstimationAdded;
use TaskManagement\Entity\Task as ReadModelTask;
use TaskManagement\SharesAssigned;
use TaskManagement\SharesSkipped;
use TaskManagement\Task;
use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\Event;
use Zend\EventManager\ListenerAggregateInterface;
use Zend\Mvc\Application;

class NotifyMailListener implements ListenerAggregateInterface
{
	/**
	 * @var MailService
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
	
	protected $listeners = array ();
	
	public function __construct(MailService $mailService, UserService $userService, TaskService $taskService) {
		$this->mailService = $mailService;
		$this->userService = $userService;
		$this->taskService = $taskService;
	}
	
	public function attach(EventManagerInterface $events) {
		$this->listeners [] = $events->getSharedManager ()->attach (Application::class, EstimationAdded::class, function (Event $event) {
			$streamEvent = $event->getTarget();
			$taskId = $streamEvent->metadata()['aggregate_id'];
			$task = $this->taskService->getTask($taskId);
			$memberId = $event->getParam ( 'by' );
			$member = $this->userService->findUser($memberId);
			$this->sendEstimationAddedInfoMail ( $task, $member );
		} );
		
		$this->listeners [] = $events->getSharedManager ()->attach (Application::class, SharesAssigned::class, array($this, 'processSharesAssigned'));
		$this->listeners [] = $events->getSharedManager ()->attach (Application::class, SharesSkipped::class, array($this, 'processSharesAssigned'));
	}
	
	public function detach(EventManagerInterface $events) {
		foreach ( $this->listeners as $index => $listener ) {
			if ($events->detach ( $listener )) {
				unset ( $this->listeners [$index] );
			}
		}
	}

	public function processSharesAssigned(Event $event) {
		$streamEvent = $event->getTarget();
		$taskId = $streamEvent->metadata()['aggregate_id'];
		$task = $this->taskService->getTask($taskId);
		$memberId = $event->getParam ( 'by' );
		$member = $this->userService->findUser($memberId);
		$this->sendSharesAssignedInfoMail ( $task, $member );
	}
	
	public function sendEstimationAddedInfoMail(Task $task, User $member){
		//OwnerInfo
		$ownerId = $task->getOwner();
		$owner = $this->userService->findUser($ownerId);
		
		//No mail to Owner for his actions
		if(strcmp($ownerId, $member->getId())==0){
			return;
		}
		
		$message = $this->mailService->getMessage();
		$message->setTo($owner->getEmail());
		$message->setSubject ( 'A member just estimated "' . $task->getSubject() . '"');
		
		$this->mailService->setTemplate( 'mail/estimation-added-info.phtml', array(
				'task' => $task,
				'recipient'=> $owner,
				'member'=> $member
		));
		
		$result = $this->mailService->send();
		return $result->isValid();
	}

	public function sendSharesAssignedInfoMail(Task $task, User $member)
	{
		//OwnerInfo
		$ownerId = $task->getOwner();
		$owner = $this->userService->findUser($ownerId);

		//No mail to Owner for his actions
		if(strcmp($ownerId, $member->getId())==0){
			return;
		}

		$message = $this->mailService->getMessage();
		$message->setTo($owner->getEmail());
		$message->setSubject ( 'A member just assigned its shares to "' . $task->getSubject() . '"' );

		$this->mailService->setTemplate( 'mail/shares-assigned-info.phtml', array(
			'task' => $task,
			'recipient'=> $owner,
			'member'=> $member
		));

		$result = $this->mailService->send();
		return $result->isValid();
	}
	
	/**
	 * Send email notification to all members with empty shares of $taskToNotify
	 * @param Task $taskToNotify
	 */
	public function remindAssignmentOfShares(ReadModelTask $task){
	
		$taskMembersWithEmptyShares = $task->findMembersWithEmptyShares();
	
		foreach ($taskMembersWithEmptyShares as $member){
				
			$message = $this->mailService->getMessage();
			$message->setTo($member->getEmail());
				
			$this->mailService->setSubject ( "O.R.A. - your contribution is required!" );
	
			$this->mailService->setTemplate( 'mail/reminder-assignment-shares.phtml', array(
					'task' => $task,
					'recipient'=> $member
			));
				
			$this->mailService->send();
		}
	}
	
	/**
	 * Send email notification to all members with no estimation of $taskToNotify
	 * @param Task $taskToNotify
	 */
	public function reminderAddEstimation(ReadModelTask $task){
		$taskMembersWithNoEstimation = $task->findMembersWithNoEstimation();
		
		foreach ($taskMembersWithNoEstimation as $member){
			$message = $this->mailService->getMessage();
			$message->setTo($member->getEmail());
			
			$this->mailService->setSubject ( "O.R.A. - your contribution is required!" );
			
			$this->mailService->setTemplate( 'mail/reminder-add-estimation.phtml', array(
					'task' => $task,
					'recipient'=> $member
			));
			
			$this->mailService->send();
		}
	}
}

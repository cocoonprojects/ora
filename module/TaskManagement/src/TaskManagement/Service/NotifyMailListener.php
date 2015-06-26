<?php

namespace TaskManagement\Service;

use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\Event;
use Zend\EventManager\ListenerAggregateInterface;
use TaskManagement\Task;
use AcMailer\Service\MailService;
use Application\Service\UserService;
use Application\Entity\User;

class NotifyMailListener implements ListenerAggregateInterface
{
	private $mailService;
	private $userService;
	
	protected $listeners = array ();
	
	public function __construct(MailService $mailService, UserService $userService) {
		$this->mailService = $mailService;
		$this->userService = $userService;
	}
	
	public function attach(EventManagerInterface $events) {
		$this->listeners [] = $events->getSharedManager ()->attach ( 'TaskManagement\TaskService', Task::EVENT_ESTIMATION_ADDED, function (Event $event) {
			$task = $event->getTarget ();
			$member = $event->getParam ( 'by' );
			$this->sendEstimationAddedInfoMail ( $task, $member );
		} );
		
		$this->listeners [] = $events->getSharedManager ()->attach ( 'TaskManagement\TaskService', Task::EVENT_SHARES_ASSIGNED, function (Event $event) {
			$task = $event->getTarget ();
			$member = $event->getParam ( 'by' );
			$this->sendSharesAssignedInfoMail ( $task, $member );
		} );
	}
	
	public function detach(EventManagerInterface $events) {
		foreach ( $this->listeners as $index => $listener ) {
			if ($events->detach ( $listener )) {
				unset ( $this->listeners [$index] );
			}
		}
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
		
		$this->mailService->setSubject ( 'A member just estimated "' . $task->getSubject() . '"');
		
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

		$this->mailService->setSubject ( 'A member just assigned its shares to "' . $task->getSubject() . '"' );

		$this->mailService->setTemplate( 'mail/shares-assigned-info.phtml', array(
			'task' => $task,
			'recipient'=> $owner,
			'member'=> $member
		));

		$result = $this->mailService->send();
		return $result->isValid();
	}
}
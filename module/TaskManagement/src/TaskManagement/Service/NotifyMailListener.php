<?php

namespace TaskManagement\Service;

use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\Event;
use Zend\EventManager\ListenerAggregateInterface;
use TaskManagement\Task;
use AcMailer\Service\MailService;
use Application\Service\UserService;
use Application\Entity\User;

class NotifyMailListener implements ListenerAggregateInterface {
	CONST ESTIMATION_ADDED = 'estimation';
	CONST SHARES_ASSIGNED = 'share';
	
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
			$this->sendMail ( $task, $member, self::ESTIMATION_ADDED );
		} );
		
		$this->listeners [] = $events->getSharedManager ()->attach ( 'TaskManagement\TaskService', Task::EVENT_SHARES_ASSIGNED, function (Event $event) {
			$task = $event->getTarget ();
			$member = $event->getParam ( 'by' );
			$this->sendMail ( $task, $member, self::SHARES_ASSIGNED );
		} );
	}
	
	public function detach(EventManagerInterface $events) {
		foreach ( $this->listeners as $index => $listener ) {
			if ($events->detach ( $listener )) {
				unset ( $this->listeners [$index] );
			}
		}
	}
	
	public function sendMail($task, $member, $trigger){
		if( !isset($task) || !isset($member) || !isset($trigger)){
			return false;
		}
		if(!(strcmp($trigger, self::ESTIMATION_ADDED)==0)&&(!strcmp($trigger, self::SHARES_ASSIGNED)==0)){
			return false;
		}
		
		//OwnerInfo
		$ownerId = $task->getOwner();
		$owner = $this->userService->findUser($ownerId);
		
		//No mail to Owner for his actions
		if(strcmp($ownerId, $member->getId())==0){
			return;
		}		
		
		$message = $this->mailService->getMessage();
		$message->setTo($owner->getEmail());
		
		$this->mailService->setSubject ( 'O.R.A. Notification Mail' );
		
		$this->mailService->setTemplate( 'mail-notification/mail-notification-template', array(
				'task' => $task,
				'owner'=> $owner,
				'member'=> $member,
				'trigger'=> $trigger
		));
		
		$result = $this->mailService->send();	
		return $result->isValid();
	}
	
}
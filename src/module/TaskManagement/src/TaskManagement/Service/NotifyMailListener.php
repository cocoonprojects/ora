<?php

namespace TaskManagement\Service;

use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\Event;
use Zend\EventManager\ListenerAggregateInterface;
use Ora\TaskManagement\Task;
use AcMailer\Service\MailService;
use Zend\Mail;
use MailNotification\Controller\MailController;
use Ora\User\UserService;
use Ora\User;
use Assetic\Exception\Exception;


class NotifyMailListener implements ListenerAggregateInterface {
	
	CONST ADD_ESTIMATION = 'estimation';
	CONST SHARES_ASSIGNED = 'share';
	
	private $mailService;
	private $userService;
	protected $listeners = array ();
	
	
	
	public function __construct(MailService $mailService, UserService $userService) {
		$this->mailService = $mailService;
		$this->userService = $userService;
	}
	public function attach(EventManagerInterface $events) {
		$this->listeners [] = $events->getSharedManager ()->attach ( 'TaskManagement\TaskService', Task::EVENT_ADD_ESTIMATION_NOTIFICATION, function (Event $event) {
			$this->sendMail ( $event->getParam ( 'taskSubject' ), $event->getParam ( 'ownerId' ), $event->getParam ( 'memberFirstName' ), $event->getParam ( 'memberLastName' ), self::ADD_ESTIMATION );
		} );
		
		$this->listeners [] = $events->getSharedManager ()->attach ( 'TaskManagement\TaskService', Task::EVENT_SHARES_ASSIGNED_NOTIFICATION, function (Event $event) {
			$this->sendMail ( $event->getParam ( 'taskSubject' ), $event->getParam ( 'ownerId' ), $event->getParam ( 'memberFirstName' ), $event->getParam ( 'memberLastName' ), self::SHARES_ASSIGNED );
		} );
	}
	public function detach(EventManagerInterface $events) {
		foreach ( $this->listeners as $index => $listener ) {
			if ($events->detach ( $listener )) {
				unset ( $this->listeners [$index] );
			}
		}
	}
	
	public function sendMail($taskSubject, $ownerId, $memberFirstName, $memberLastName, $trigger) {
		
		$owner = $this->userService->findUser($ownerId);
		
		$ownerName=$owner->getFirstname();
		$ownerMail=$owner->getEmail();
		
		$message = $this->mailService->getMessage ();
		$message->setTo ( $ownerMail );
		
		$this->mailService->setSubject ( 'O.R.A. Notification Mail' );
		
		// Set template
		$this->mailService->setTemplate ( 'mail-notification/mail-notification-template', array (
				'ownerName' => $ownerName,
				'trigger' => $trigger,
				'taskSubject' => $taskSubject,
				'memberFirstName' => $memberFirstName,
				'memberLastName' => $memberLastName 
		) );
		$result = $this->mailService->send ();
		
		return $result;
		
		/*if($result->hasException()){
			throw $result->getException();
		}*/
	}
}
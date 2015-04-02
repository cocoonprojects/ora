<?php

namespace TaskManagement\Service;

use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\Event;
use Zend\EventManager\ListenerAggregateInterface;
use Ora\TaskManagement\Task;
use AcMailer\Service\MailService;
use Zend\Mail;

class NotifyMailListener implements ListenerAggregateInterface {
	CONST ADD_ESTIMATION = 'estimation';
	CONST SHARES_ASSIGNED = 'share';
	private $mailService;
	protected $listeners = array ();
	public function __construct(MailService $mailService) {
		$this->mailService = $mailService;
	}
	public function attach(EventManagerInterface $events) {
		$this->listeners [] = $events->getSharedManager ()->attach ( 'TaskManagement\TaskService', Task::EVENT_ADD_ESTIMATION_NOTIFICATION, function (Event $event) {
			$this->sendMail ( $event->getParam ( 'taskSubject' ), $event->getParam ( 'ownerName' ), $event->getParam ( 'ownerMail' ), $event->getParam ( 'memberFirstName' ), $event->getParam ( 'memberLastName' ), self::ADD_ESTIMATION );
		} );
		
		$this->listeners [] = $events->getSharedManager ()->attach ( 'TaskManagement\TaskService', Task::EVENT_SHARES_ASSIGNED_NOTIFICATION, function (Event $event) {
			$this->sendMail ( $event->getParam ( 'taskSubject' ), $event->getParam ( 'ownerName' ), $event->getParam ( 'ownerMail' ), $event->getParam ( 'memberFirstName' ), $event->getParam ( 'memberLastName' ), self::SHARES_ASSIGNED );
		} );
	}
	public function detach(EventManagerInterface $events) {
		foreach ( $this->listeners as $index => $listener ) {
			if ($events->detach ( $listener )) {
				unset ( $this->listeners [$index] );
			}
		}
	}
	public function sendMail($taskSubject, $ownerName, $ownerMail, $memberFirstName, $memberLastName, $trigger) {
		$message = $this->mailService->getMessage ();
		$message->setTo ( $ownerMail );
		
		$this->mailService->setSubject ( 'O.R.A. Notification Mail' );
		// TODO: set template
		$this->mailService->setTemplate ( '', array (
				'ownerName' => $ownerName,
				'trigger' => $trigger,
				'taskSubject' => $taskSubject,
				'memberFirstName' => $memberFirstName,
				'memberLastName' => $memberLastName 
		) );
		$this->mailService->send ();
	}
}
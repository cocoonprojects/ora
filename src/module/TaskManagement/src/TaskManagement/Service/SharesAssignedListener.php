<?php

namespace TaskManagement\Service;

use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\Event;
use Zend\EventManager\ListenerAggregateInterface;
use Ora\TaskManagement\Task;

class SharesAssignedListener implements ListenerAggregateInterface {
	
	private $mailService;
	protected $listeners = array ();
	
	public function __construct(MailService $mailService) {
		$this->mailService = $mailService;
	}
	public function attach(EventManagerInterface $events) {
		$this->listeners [] = $events->getSharedManager ()->attach ( 'TaskManagement\TaskService', Task::EVENT_SHARES_ASSIGNED_NOTIFICATION, function (Event $event) {
				
			$taskId = $event->getParam ( 'taskId' );
			$owner_name = $event->getParam ( 'owner_name' );
			$owner_mail = $event->getParam ( 'owner_mail' );
			$member_mail = $event->getParam ( 'member_mail' );
				
			// send mail
			$message = $this->mailService->getMessage ();
			$message->setTo ( $owner_mail );
				
			// TODO: set template
			$this->mailService->setSubject ( 'Shares Assigned Mail' );
			$mailService->setTemplate ( '', array (
					'owner_name' => $owner_name,
					'member_mail' => $member_mail,
					'task_id' => $taskId
			) );
			try {
				$result = $this->mailService->send ();
			} catch ( \Exception $e ) {
				//TODO: use page error
				echo "Exception!! " . $e->getMessage ();
			}
		} );
	}
	public function detach(EventManagerInterface $events) {
		foreach ( $this->listeners as $index => $listener ) {
			if ($events->detach ( $listener )) {
				unset ( $this->listeners [$index] );
			}
		}
	}
}
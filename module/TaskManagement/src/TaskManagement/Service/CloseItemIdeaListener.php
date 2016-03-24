<?php

namespace TaskManagement\Service;

use Application\Service\UserService;
use Prooph\EventStore\EventStore;
use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\Event;
use Zend\EventManager\ListenerAggregateInterface;
use Zend\Mvc\Application;
use People\Service\OrganizationService;
use TaskManagement\ApprovalCreated;
use TaskManagement\Entity\Task as ReadModelTask;
use TaskManagement\Entity\Vote;

class CloseItemIdeaListener implements ListenerAggregateInterface {
	protected $listeners = array ();
	/**
	 *
	 * @var OrganizationService
	 */
	protected $organizationService;
	
	/**
	 *
	 * @var TaskService
	 */
	protected $taskService;
	/**
	 *
	 * @var UserService
	 */
	protected $userService;
	
	/**
	 *
	 * @var EventStore
	 */
	private $transactionManager;
	public function __construct(TaskService $taskService, UserService $userService, OrganizationService $organizationService, EventStore $transactionManager) {
		$this->taskService = $taskService;
		$this->organizationService = $organizationService;
		$this->transactionManager = $transactionManager;
		$this->userService = $userService;
	}
	public function attach(EventManagerInterface $events) {
		$this->listeners [] = $events->getSharedManager ()->attach ( Application::class, ApprovalCreated::class, array (
				$this,
				'processEvent' 
		) );
	}
	public function processEvent(Event $event) {
		$streamEvent = $event->getTarget ();
		$taskId = $streamEvent->metadata ()['aggregate_id'];
		$task = $this->taskService->getTask ( $taskId );
		$ownerid = $task->getOwner ();
		$owner = $this->userService->findUser ( $ownerid );
		$byId = $event->getParam ( 'by' );
		$organization = $this->organizationService->findOrganization ( $task->getOrganizationId () );
		$memberhipcount = $this->organizationService->countOrganizationMemberships ( $organization );
		$taskReadModel = $this->taskService->findTask ( $taskId );
		$approvals = $taskReadModel->getApprovals ();
		
		$accept = 0;
		$reject = 0;
		$abstain = 0;
		foreach ( $approvals as $approval ) {
			switch ($approval->getVote ()->getValue ()) {
				case Vote::VOTE_FOR :
					$accept ++;
					break;
				case Vote::VOTE_AGAINST :
					$reject ++;
					break;
				case Vote::VOTE_ABSTAIN :
					$abstain ++;
					break;
			}
		}
		if ($accept > $memberhipcount / 2) {
			
			$this->transactionManager->beginTransaction ();
			try {
				$task->open ( $owner );
				$this->transactionManager->commit ();
			} catch ( \Exception $e ) {
				$this->transactionManager->rollback ();
				throw $e;
			}
		} elseif ($reject > $memberhipcount / 2) {
			
			$this->transactionManager->beginTransaction ();
			try {
				$task->archive ( $owner );
				$this->transactionManager->commit ();
			} catch ( \Exception $e ) {
				var_dump ( $e );
				$this->transactionManager->rollback ();
				throw $e;
			}
		} elseif ($memberhipcount == (count ( $approvals ))) {
			
			if ($accept > $reject) {
				
				$this->transactionManager->beginTransaction ();
				try {
					$task->open ( $owner );
					$this->transactionManager->commit ();
				} catch ( \Exception $e ) {
					$this->transactionManager->rollback ();
					throw $e;
				}
			} else {
				$this->transactionManager->beginTransaction ();
				try {
					$task->archive ( $owner );
					$this->transactionManager->commit ();
				} catch ( \Exception $e ) {
					$this->transactionManager->rollback ();
					throw $e;
				}
			}
		}
	}
	public function detach(EventManagerInterface $events) {
		if ($events->getSharedManager ()->detach ( 'TaskManagement\TaskService', $this->listeners [0] )) {
			unset ( $this->listeners [0] );
		}
	}
}
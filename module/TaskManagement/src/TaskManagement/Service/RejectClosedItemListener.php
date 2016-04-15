<?php

namespace TaskManagement\Service;

use Application\Service\UserService;
use Prooph\EventStore\EventStore;
use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\Event;
use Zend\EventManager\ListenerAggregateInterface;
use Zend\Mvc\Application;
use People\Service\OrganizationService;
use TaskManagement\AcceptancesRemoved;
use TaskManagement\Entity\Task as ReadModelTask;
use TaskManagement\Entity\Vote;

class RejectClosedItemListener implements ListenerAggregateInterface {
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
		$this->listeners [] = $events->getSharedManager ()->attach ( Application::class, AcceptancesRemoved::class, array (
				$this,
				'processEvent' 
		) );
	}
	public function processEvent(Event $event) {
		$streamEvent = $event->getTarget ();
		$taskId = $streamEvent->metadata ()['aggregate_id'];
		$task = $this->taskService->getTask ( $taskId );
			
		$this->transactionManager->beginTransaction ();
		try {
			$task->removeAcceptances();
			$this->transactionManager->commit ();
		} catch ( \Exception $e ) {
			$this->transactionManager->rollback ();
			throw $e;
		}
	}
	public function detach(EventManagerInterface $events) {
		if ($events->getSharedManager ()->detach ( 'TaskManagement\TaskService', $this->listeners [0] )) {
			unset ( $this->listeners [0] );
		}
	}
}
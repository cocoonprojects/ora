<?php

namespace Ora\Kanbanize;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Ora\Kanbanize\KanbanizeTask;
use Ora\Kanbanize\KanbanizeAPI;
use Ora\Kanbanize\KanbanizeService;
use Ora\EventStore\EventStore;
use Ora\EntitySerializer;

/**
 * Service Kanbanize
 *
 * @author Andrea Lupia <alupia@dimes.unical.it>
 *
 */
class EventSourcingKanbanizeService implements KanbanizeService 
{
	
	/**
	 * Event Manager
	 *
	 * @var \Zend\EventManager\EventManagerInterface
	 */
	private $eventManager;
	
	/**
	 * Kanbanize API
	 *
	 * @var \Ora\Kanbanize\KanbanizeAPI
	 */
	private $kanbanize;
	private $entityManager;
	private $eventStore;
	private $entitySerializer;
	
	/*
	 * Constructs service
	 */
	public function __construct($entityManager, EventStore $eventStore, EntitySerializer $entitySerializer, $apiKey, $url) {
		$this->entityManager = $entityManager;
		$this->eventStore = $eventStore;
		$this->entitySerializer = $entitySerializer;
		$this->kanbanize = new KanbanizeAPI ();
		$this->kanbanize->setApiKey ( $apiKey );
		$this->kanbanize->setUrl ( $url );
	}

	public function moveTask(KanbanizeTask $kanbanizeTask, $status) {
		$movedAt = new \DateTime();
		
  		$boardId = $kanbanizeTask->getBoardId();
  		$taskId = $kanbanizeTask->getTaskId();
  		$task = $this->kanbanize->getTaskDetails($boardId, $taskId);
  		if(isset($task['Error'])) {
  			return $task['Error'];
  		}
  		else if($task['columnname'] == $status) {
  			//Task already in this column, nothing to do
  			return 0;
  		}
  		else {
  			$this->kanbanize->moveTask($boardId, $taskId, $status);
  			
  			$kanbanizeTask->setStatus(KanbanizeTask::getMappedStatus($status));
  			
  			$event = new KanbanizeTaskMovedEvent($movedAt, $kanbanizeTask, $this->entitySerializer);
  			
  			$this->eventStore->appendToStream($event);
  			
  			return 1;
  		}
	}

	/**
	 *
	 * @param        	
	 *
	 */
	public function createNewTask($projectId, $taskSubject, $boardId) {
		$createdAt = new \DateTime ();
		
		// TODO: Modificare createdBy per inserire User
		$createdBy = "NOME UTENTE INVENTATO";
		
		$options = array (
				'description' => $taskSubject 
		);
		$id = $this->kanbanize->createNewTask ( $boardId, $options );
		if (is_null ( $id )) {
			return 0;
		} else {
			$kanbanizeTask = new KanbanizeTask ( uniqid (), $boardId, $id, $createdAt, $createdBy );
			// TODO $event = new KanbanizeTaskCreatedEvent($createdAt, $kanbanizeTask, $this->entitySerializer);
			// TODO $this->eventStore->appendToStream($event);
			return 1;
		}
	}
	
	/**
	 *
	 * @param int		$boardId
	 * @param string	$status
	 * 
	 *
	 */
	public function getTasks($boardId, $status = null) {
		$tasks_to_return = array ();
		$tasks = $this->kanbanize->getAllTasks ( $boardId );
		if (is_null ( $status ))
			return $tasks;
		else {
			foreach ( $tasks as $singletask ) {
				if ($singletask ["columnname"] == $status)
					$tasks_to_return [] = $singletask;
			}
			
			return $tasks_to_return;
		}
	}

	public function isAcceptable(KanbanizeTask $kanbanizeTask) {
		//Check if the task is already accepted
		$boardId = $kanbanizeTask->getBoardId();
		$taskId = $kanbanizeTask->getTaskId();
		$task = $this->kanbanize->getTaskDetails($boardId, $taskId);
		return $task['columnname'] != KanbanizeTask::COLUMN_ACCEPTED;
		//TODO check if all team as evaluated the task
	}

	public function canBeMovedBackToOngoing(KanbanizeTask $kanbanizeTask) {
		//Check if the task can be moved back to ongoing
		$boardId = $kanbanizeTask->getBoardId();
		$taskId = $kanbanizeTask->getTaskId();
		$task = $this->kanbanize->getTaskDetails($boardId, $taskId);
		return $task['columnname'] == KanbanizeTask::COLUMN_COMPLETED || $task['columnname'] == KanbanizeTask::COLUMN_ACCEPTED;
		//TODO other controls to do?
	}
	
}
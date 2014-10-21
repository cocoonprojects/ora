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
  			//return 0;
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
		
		// /**
		// * @param KanbanizeTask $kanbanizeTask
		// */
		// public function acceptTask($kanbanizeTask) {
		// $editedAt = new \DateTime();
		
	// $boardId = $kanbanizeTask->getBoardId();
		// $taskId = $kanbanizeTask->getTaskId();
		// $task = $this->kanbanize->getTaskDetails($boardId, $taskId);
		// if(isset($task['Error'])) {
		// //return 0;
		// return $task['Error'];
		// }
		// if($task['columnname'] != KanbanizeTask::COLUMN_ACCEPTED) {
		
	// //Task can be accepted
		// $this->kanbanize->moveTask($boardId, $taskId, KanbanizeTask::COLUMN_ACCEPTED);
		
	// $kanbanizeTask->setStatus(KanbanizeTask::getMappedStatus(KanbanizeTask::COLUMN_ACCEPTED));
		
	// $event = new KanbanizeTaskMovedEvent($editedAt, $kanbanizeTask, $this->entitySerializer);
		
	// $this->eventStore->appendToStream($event);
		
	// return 1;
		// }
		// else {
		// //Task is already Accepted, nothing to do
		// return 0;
		// }
		// }
		
	// public function moveTaskBackToOngoing($kanbanizeTask) {
		// $boardId = $kanbanizeTask->getBoardId();
		// $taskId = $kanbanizeTask->getTaskId();
		// $task = $this->kanbanize->getTaskDetails($boardId, $taskId);
		// if(isset($task['Error'])) {
		// //return 0;
		// return $task['Error'];
		// }
		// if($task['columnname'] != KanbanizeTask::COLUMN_ONGOING &&
		// ($task['columnname'] == KanbanizeTask::COLUMN_COMPLETED || $task['columnname'] == KanbanizeTask::COLUMN_ACCEPTED)) {
		
	// //Task can be moved back to ongoing
		// $this->kanbanize->moveTask($boardId, $taskId, KanbanizeTask::COLUMN_ONGOING);
		
	// $kanbanizeTask->setStatus(KanbanizeTask::getMappedStatus(KanbanizeTask::COLUMN_ONGOING));
		
	// $event = new KanbanizeTaskMovedEvent($editedAt, $kanbanizeTask, $this->entitySerializer);
		
	// $this->eventStore->appendToStream($event);
		
	// return 1;
		// }
		// else {
		// //Task is already Accepted, nothing to do
		// return 0;
		// }
		// }
	
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
	 * @param        	
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
  
}
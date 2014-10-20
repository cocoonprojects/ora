<?php

namespace Ora\Kanbanize;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Ora\TaskManagement\Task;
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
  /**
   * 
   *
   */
  private $entityManager; 
  
  private $eventStore;
  
  private $entitySerializer;
  
  /*
   * Constructs service 
   * 
   */
  public function __construct($entityManager, EventStore $eventStore, EntitySerializer $entitySerializer, $apiKey, $url) 
  {
	$this->entityManager = $entityManager;
	$this->eventStore = $eventStore;
	$this->entitySerializer = $entitySerializer;
  	$this->kanbanize = new KanbanizeAPI();
  	$this->kanbanize->setApiKey($apiKey);
  	$this->kanbanize->setUrl($url);
  }

  /**
   * @param KanbanizeTask $kanbanizeTask
   */
  public function acceptTask($kanbanizeTask) {
  	$editedAt = new \DateTime();
  	
  	$boardId = $kanbanizeTask->getBoardId();
  	$taskId = $kanbanizeTask->getTaskId();
  	$task = $this->kanbanize->getTaskDetails($boardId, $taskId);
  	if(isset($task['Error'])) {
  		//return 0;
  		return $task['Error'];
  	}
  	if($task['columnname'] != self::COLUMN_ACCEPTED) {
  		
  		//Task can be accepted
  		$this->kanbanize->moveTask($boardId, $taskId, self::COLUMN_ACCEPTED);
  		
  		$event = new KanbanizeTaskMovedEvent($editedAt, $task, $entitySerializer);
  		
  		$this->eventStore->appendToStream($event);
  		
  		return 1;
  	}
  	else {
  		//Task is already Accepted, nothing to do
  		return 0;
  	}
  }
  
  /**
   * @param 
   */
  
  public function createNewTask($projectId, $taskSubject, $boardId){
  	
  	$createdAt = new \DateTime();
  	
  	$options = array('description' => $taskSubject);
  	$id = $this->kanbanize->createNewTask($boardId, $options);
  	if(is_null($id)) {
  		return 0;
  	}
  	else {
  		$kanbanizeTask = new KanbanizeTask(uniqid(), $boardId, $id, $createdAt);
  		//TODO $event = new KanbanizeTaskCreatedEvent($createdAt, $kanbanizeTask, $this->entitySerializer);
  		//TODO $this->eventStore->appendToStream($event);
  		return 1;
  	}
  }
  
  /**
   * @param 
   */
  
  public function getTasks($boardId, $status){
 	$tasks_to_return = array();
  	$tasks = $this->kanbanize->getAllTasks($boardId);
  	foreach ($tasks as $singletask ){
  		if ($singletask["columnname"]==$status)
  			$tasks_to_return[]=$singletask;
  	}
  	
  	return $tasks_to_return;
  	
  }
  
}
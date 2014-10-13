<?php

namespace Ora\TaskManagement;

use Ora\EventStore\EventStore;
use Ora\EntitySerializer;


class EventSourcingTaskService implements TaskService
{
    private $eventStore;
    private $entitySerializer;
    
    public function __construct(EventStore $eventStore, EntitySerializer $entitySerializer)
    {
        $this->eventStore = $eventStore;
	    $this->entitySerializer = $entitySerializer;
    }
	
	public function createNewTask($project, $taskSubject)
	{
	    $createdAt = new \DateTime();
        
	    // Generate unique ID for Task
	    $taskID = uniqid();   
	    
	    // Creation of new task entity
	    $task = new Task($taskID, $createdAt);
	    // TODO: Controllare se descrizione e projectid vanno nel costruttore in quanto obbligatori
	    $task->setSubject($taskSubject);
	    $task->setProject($project);
	    
	    // Creation of event after creation of Task Entity
	    $event = new TaskCreatedEvent($createdAt, $task, $this->entitySerializer);
	    
	    // Serialize TASK ENTITY to JSON
	   // $taskSerialized = $this->entitySerializer->toJson($task);
	    
	    $this->eventStore->appendToStream($event);
	}
}
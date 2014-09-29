<?php

namespace Ora\TaskManagement;

use Ora\EventStore\EventStore;

class EventSourcingTaskService implements TaskService
{
    private $eventStore;
    
    public function __construct(EventStore $eventStore)
    {
        $this->eventStore = $eventStore;
    }
	
	public function createNewTask($parentProject, $taskDescription)
	{
	    $createdAt = new \DateTime();
	    
	    // Generate unique ID for Task
	    $taskID = uniqid();   
	     
	    // Creation of new task entity
	    $task = new TaskEntity($taskID, $createdAt, $this->eventStore);
	    // TODO: Controllare se descrizione e projectid vanno nel costruttore in quanto obbligatori
	    $task->setDescription($taskDescription);
	    $task->setProject($parentProject);
	    
	    // Creation of event after creation of Task Entity
	    //$event = new TaskCreated($createdAt, $task);
	    //$this->eventStore->appendToStream($event);
	    
	    // TODO: Quando sarà presente l'entità PROJECT, riattivare questo rigo
	    //$projectID = $parentProject->getId();
	    $projectID = "PROJECT_ID_INVENTATO";
	    
	    $data = array(
	        "projectID"=>$projectID,
	        "taskDescription"=>$taskDescription
	    );
	    
	    return $data;
	}
}
<?php

namespace Ora\TaskManagement;

use Ora\EventStore\EventStore;
use Ora\EntitySerializer;


/**
 * @author Giannotti Fabio
 */
class EventSourcingTaskService implements TaskService
{
    private $entityManager;
    private $eventStore;
    private $entitySerializer;
    
    public function __construct($entityManager, EventStore $eventStore, EntitySerializer $entitySerializer)
    {
        $this->entityManager = $entityManager;
        $this->eventStore = $eventStore;
	    $this->entitySerializer = $entitySerializer;
    }
	
	public function createNewTask($project, $taskSubject)
	{
	    $createdAt = new \DateTime();
	    // TODO: Modificare createdBy per inserire lo USERNAME esatto
	    // prelevando il nome utente dalla sessione o da dove sia
	    $createdBy = $this->entityManager->getRepository('Ora\User\User')->findOneBy(array("id"=>"1"));
        
	    // Generate unique ID for Task
	    $taskID = uniqid();   
	    
	    // Creation of new task entity
	    $task = new Task($taskID, $createdAt, $createdBy);

	    // TODO: Controllare se descrizione e projectid vanno nel costruttore in quanto obbligatori
	    $task->setSubject($taskSubject);
	    $task->setProject($project);
	    
	    // Creation of event after creation of Task Entity
	    $event = new TaskCreatedEvent($createdAt, $task, $this->entitySerializer);
	    
	    $this->eventStore->appendToStream($event);
	}
	
	public function findTaskByID($id)
	{
	    $task = $this->entityManager->getRepository('Ora\TaskManagement\Task')->findOneBy(array("id"=>$id));
	     
	    return $task;
	}
	
	public function editTask($task, $data)
	{
	    $editedAt = new \DateTime();
	    // TODO: Modificare createdBy per inserire lo USERNAME esatto
	    // prelevando il nome utente dalla sessione o da dove sia
	    $editedBy = $this->entityManager->getRepository('Ora\User\User')->findOneBy(array("id"=>"1"));
	    
	    // Check updated fields
	    if (isset($data['subject']))
	        $task->setSubject($data['subject']);
	    
	    $task->setMostRecentEditAt($editedAt);
	    $task->setMostRecentEditBy($editedBy);
	     
	    // Creation of event after creation of Task Entity
	    $event = new TaskEditedEvent($editedAt, $task, $this->entitySerializer);
	    
	    $this->eventStore->appendToStream($event);
	}
	
	public function listAvailableTasks()
	{
	    $serializedTasks['tasks'] = array();
	    
	    $tasks = $this->entityManager->getRepository('Ora\TaskManagement\Task')->findAll();	    
	    foreach ($tasks as $task)
	    {
	        $serializedTasks['tasks'][] = $task->serializeToARRAY($this->entitySerializer);
	    }
	    
	    // TODO: Eliminare task temporaneo creato solo per "popolare" il JSON
	    // Si potrebbe evitare di generare qui i fake tasks lanciando uno script php
	    // per popolare la tabella dei tasks in modo da poterne recuperare qualcuno
	    $serializedTasks['tasks'][] = array(
	        "ID"=>"2j3h42ffgj34",
	        "subject"=>"Ultima descrizione farlocca per popolare tabella",
	        "created_at"=>new \Datetime(),
	        "created_by"=>"Roberta",
	        "members"=>array(0=>"Roberta", 1=>"Giovanni"),
	        "status"=>40
	    );
	    
	    return $serializedTasks;
	}
}
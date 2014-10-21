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
	    $createdBy = "NOME UTENTE INVENTATO";
        
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
	        "ID"=>"d9f8s9fd8sdf",
	        "subject"=>"Descrizione casuale di un task già esistente",
	        "created_at"=>new \Datetime(),
	        "created_by"=>"Fabio",
	        "members"=>array(0=>"Fabio"),
	        "status"=>20
	    );	    
	    $serializedTasks['tasks'][] = array(
	        "ID"=>"f7g6h6fgh7do",
	        "subject"=>"Seconda descrizione casuale per task disponibili",
	        "created_at"=>new \Datetime(),
	        "created_by"=>"Mario",
	        "members"=>array(0=>"Mario"),
	        "status"=>20
	    );
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
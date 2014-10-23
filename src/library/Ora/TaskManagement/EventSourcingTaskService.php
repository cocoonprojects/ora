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
	
    /**
     * Append new event for CREATE new task with specified parameters
     */
	public function createNewTask(\Ora\ProjectManagement\Project $project, $taskSubject)
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
	    
	    $event = new TaskCreatedEvent($createdAt, $task, $this->entitySerializer);
	    
	    $this->eventStore->appendToStream($event);
	}
	
	/**
	 * Retrieve task entity with specified ID
	 */
	public function findTask($id)
	{
	    $task = $this->entityManager->getRepository('Ora\TaskManagement\Task')->findOneBy(array("id"=>$id));
	     
	    return $task;
	}
	
	/**
	 * Append new event for UPDATE the specified task entity with $data parameters
	 */
	public function editTask(\Ora\TaskManagement\Task $task)
	{
	    $editedAt = new \DateTime();
	    // TODO: Modificare editedBy per inserire lo USERNAME esatto
	    // prelevando il nome utente dalla sessione o da dove sia
	    $editedBy = $this->entityManager->getRepository('Ora\User\User')->findOneBy(array("id"=>"1"));
	    	    
	    $task->setMostRecentEditAt($editedAt);
	    $task->setMostRecentEditBy($editedBy);
	    
	    $event = new TaskEditedEvent($editedAt, $task, $this->entitySerializer);
	    
	    $this->eventStore->appendToStream($event);
	}
	
	/**
	 * Append new event for DELETE the specified task entity
	 */
	public function deleteTask(\Ora\TaskManagement\Task $task)
	{
	    $deletedAt = new \DateTime();
	    
	    $event = new TaskDeletedEvent($deletedAt, $task, $this->entitySerializer);
	    
	    $this->eventStore->appendToStream($event);
	}
	
	/**
	 * Get the list of all available tasks 
	 */
	public function listAvailableTasks()
	{
	    $json['tasks'] = array();
	    
	    $tasks = $this->entityManager->getRepository('Ora\TaskManagement\Task')->findAll();	    
	    
	    foreach ($tasks as $task)
	    {
	        $json['tasks'][] = $task->serializeToARRAY($this->entitySerializer);
	    }
	    
	    // TODO: Tornare sul client le informazioni riguardanti 
	    // l'utente loggato recuperandole dalla sessione
	    $json['loggeduser'] = array();
	    $json['loggeduser']['id'] = "1";
	    
	    return $json;
	}
	
	/**
	 * Add new USER (member) into TEAM of specified TASK
	 */
	public function addTaskMember(\Ora\TaskManagement\Task $task, \Ora\User\User $user)
	{
	    $joinedAt = new \DateTime();
	    
        $task->addMember($user);
        
	    $event = new TaskMemberAddedEvent($joinedAt, $task, $this->entitySerializer);
	     
	    $this->eventStore->appendToStream($event);
	}
	
	/**
	 * Remove USER (member) from TEAM of specified TASK
	 */
	public function removeTaskMember(\Ora\TaskManagement\Task $task, \Ora\User\User $user)
	{
	    $joinedAt = new \DateTime();
	     
        $task->removeMember($user);
	
        $event = new TaskMemberRemovedEvent($joinedAt, $task, $this->entitySerializer);
	
        $this->eventStore->appendToStream($event);
	}
}
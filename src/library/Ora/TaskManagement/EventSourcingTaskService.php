<?php

namespace Ora\TaskManagement;

use Ora\EntitySerializer;
use Prooph\EventStore\EventStore;
use Prooph\EventStore\Aggregate\AggregateRepository;
use Prooph\EventStore\Stream\StreamStrategyInterface;
use Prooph\EventSourcing\EventStoreIntegration\AggregateTranslator;
use Prooph\EventStore\Aggregate\AggregateType;
use Ora\User\User;
use Ora\ProjectManagement\Project;
use Zend\Captcha\Exception\DomainException;
use Ora\IllegalStateException;

/**
 * @author Giannotti Fabio
 */
class EventSourcingTaskService extends AggregateRepository implements TaskService
{
    
    public function __construct(EventStore $eventStore, StreamStrategyInterface $eventStoreStrategy)
    {
		parent::__construct($eventStore, new AggregateTranslator(), $eventStoreStrategy, new AggregateType('Ora\TaskManagement\Task'));
	}
	
	public function createTask(Project $project, $subject, User $createdBy)
	{
		$this->eventStore->beginTransaction();
	    $task = Task::create($project, $subject, $createdBy);
	    $this->addAggregateRoot($task);
		$this->eventStore->commit();
		return $task;
	}
	
	/**
	 * Retrieve task entity with specified ID
	 */
	public function getTask($id)
	{
		try {
		    $task = $this->getAggregateRoot($this->aggregateType, $id);
		    return $task;
        } catch (\RuntimeException $e) {
        	return null;
        }
	}
	
	/**
	 * Append new event for UPDATE the specified task entity with $data parameters
	 */
	public function editTask(Task $task)
	{
		$this->eventStore->beginTransaction();
		$this->eventStore->commit();
	}
	
	/**
	 * Append new event for DELETE the specified task entity
	 */
	public function deleteTask(Task $task, User $deletedBy)
	{
		$this->eventStore->beginTransaction();
		$task->delete($deletedBy);    
	    $this->eventStore->commit();
	}
	
	/**
	 * Get the list of all available tasks 
	 */
	public function listAvailableTasks()
	{
	    $json['tasks'] = array();
	    
	    $tasks = $this->entityManager->getRepository('Ora\TaskManagement\Task')->findAll();	    
	    
	    // TODO: Tornare sul client le informazioni riguardanti l'utente loggato 
	    // recuperandole dalla sessione. Al momento forzo sempre il ritorno di "1"
	    $loggedUserID = "1";
	    $json['loggeduser'] = array();
	    $json['loggeduser']['id'] = $loggedUserID;
	    
	    foreach ($tasks as $task)
	    {
	        $serializedTask = $task->serializeToARRAY($this->entitySerializer);
	        $serializedTask['alreadyMember'] = false;
	        
	        // TODO: Verificare se è possibile stabilire più facilmente se l'utente attualmente
	        // loggato fa parte oppure no dei membri del team relativo all'attuale task serializzato.
	        // Questa cosa serve per stabilire cosa mostrare e cosa no nell'interfaccia utente sul client.
	        foreach ($task->getMembers() as $member)
	        {
	            if ($member->getId() == $loggedUserID)
	               $serializedTask['alreadyMember'] = true; 
	        }
	        
	        $json['tasks'][] = $serializedTask;
	    }
	        
	    return $json;
	}
}
<?php

namespace Ora\TaskManagement;

use Doctrine\ORM\EntityManager;
use Prooph\EventStore\EventStore;
use Prooph\EventStore\Aggregate\AggregateRepository;
use Prooph\EventStore\Stream\StreamStrategyInterface;
use Prooph\EventSourcing\EventStoreIntegration\AggregateTranslator;
use Prooph\EventStore\Aggregate\AggregateType;
use Ora\User\User;
use Ora\ProjectManagement\Project;
use Ora\IllegalStateException;

/**
 * @author Giannotti Fabio
 */
class EventSourcingTaskService extends AggregateRepository implements TaskService
{
	private $entityManager;
    
    public function __construct(EventStore $eventStore, StreamStrategyInterface $eventStoreStrategy, EntityManager $entityManager)
    {
		parent::__construct($eventStore, new AggregateTranslator(), $eventStoreStrategy, new AggregateType('Ora\TaskManagement\Task'));
		$this->entityManager = $entityManager;
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
		$repository = $this->entityManager->getRepository('Ora\TaskManagement\Task');
	    return $repository->findAll();	    
	}
	
	public function findTaskById($id) {
		return $this->entityManager->find('Ora\TaskManagement\Task', $id);
	}
}
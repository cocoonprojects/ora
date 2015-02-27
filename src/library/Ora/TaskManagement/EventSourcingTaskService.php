<?php

namespace Ora\TaskManagement;

use Doctrine\ORM\EntityManager;
use Prooph\EventStore\EventStore;
use Prooph\EventStore\Aggregate\AggregateRepository;
use Prooph\EventStore\Stream\MappedSuperclassStreamStrategy;
use Prooph\EventSourcing\EventStoreIntegration\AggregateTranslator;
use Prooph\EventStore\Aggregate\AggregateType;
use Ora\User\User;
use Ora\StreamManagement\Stream;

use Ora\ReadModel\Share;

use BjyAuthorize\Service\Authorize;


/**
 * @author Giannotti Fabio
 */
class EventSourcingTaskService extends AggregateRepository implements TaskService
{
	/**
	 * 
	 * @var EntityManager
	 */
	private $entityManager;
	/**
	 * 
	 * @var AggregateType
	 */
	private $aggregateRootType;
	
	private $authorize;
		
    public function __construct(EventStore $eventStore, EntityManager $entityManager, Authorize $authorize)
    {
    	$this->aggregateRootType = new AggregateType('Ora\\TaskManagement\\Task');
		parent::__construct($eventStore, new AggregateTranslator(), new MappedSuperclassStreamStrategy($eventStore, $this->aggregateRootType, [$this->aggregateRootType->toString() => 'event_stream']));
		$this->entityManager = $entityManager;
		$this->authorize = $authorize;	
	}
	
	public function createTask(Stream $stream, $subject, User $createdBy)
	{			
		$this->eventStore->beginTransaction();
	    $task = Task::create($stream, $subject, $createdBy);
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
			$task = $this->getAggregateRoot($this->aggregateRootType, $id);
		    return $task;
        } catch (\RuntimeException $e) {
        	return null;
        }
	}
	
	/**
	 * Get the list of all available tasks 
	 */
	public function findTasks()
	{
		$repository = $this->entityManager->getRepository('Ora\ReadModel\Task');
	    return $repository->findBy(array(), array('mostRecentEditAt' => 'DESC'));	    
	}
	
	public function findTask($id) {
		return $this->entityManager->find('Ora\ReadModel\Task', $id);
	}
	
	public function findStreamTasks($streamId)
	{	
		$repository = $this->entityManager->getRepository('Ora\ReadModel\Task')->findBy(array('stream' => $streamId));
	    return $repository;

	}
}
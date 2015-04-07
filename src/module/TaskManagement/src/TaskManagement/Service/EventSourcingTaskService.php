<?php

namespace TaskManagement\Service;

use Zend\EventManager\EventManagerAwareInterface;
use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\EventManager;
use Zend\EventManager\Event;
use Doctrine\ORM\EntityManager;
use Prooph\EventStore\EventStore;
use Prooph\EventStore\Aggregate\AggregateRepository;
use Prooph\EventStore\Aggregate\AggregateType;
use Prooph\EventStore\Stream\SingleStreamStrategy;
use Prooph\EventSourcing\EventStoreIntegration\AggregateTranslator;
use Rhumsaa\Uuid\Uuid;
use Ora\User\User;
use TaskManagement\Stream;
use TaskManagement\Task;

class EventSourcingTaskService extends AggregateRepository implements TaskService, EventManagerAwareInterface
{
	/**
	 * 
	 * @var EntityManager
	 */
	private $entityManager;
	/**
	 * 
	 * @var EventManagerInterface
	 */
	private $events;
    
    public function __construct(EventStore $eventStore, EntityManager $entityManager)
    {
		parent::__construct($eventStore, new AggregateTranslator(), new SingleStreamStrategy($eventStore), AggregateType::fromAggregateRootClass(Task::class));
		$this->entityManager = $entityManager;	
	}
	
	public function addTask(Task $task)
	{			
	    $task->setEventManager($this->getEventManager());
		$this->addAggregateRoot($task);
		return $task;
	}
	
	/**
	 * Retrieve task entity with specified ID
	 */
	public function getTask($id)
	{
		$tId = $id instanceof Uuid ? $id->toString() : $id;
		$task = $this->getAggregateRoot($tId);
		if($task != null) {
			$task->setEventManager($this->getEventManager());
		}
		return $task;
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
	
	public function findStreamTasks($streamId) {	
		$repository = $this->entityManager->getRepository('Ora\ReadModel\Task')->findBy(array('stream' => $streamId));
	    return $repository;
	}
	
	public function setEventManager(EventManagerInterface $events) {
		$events->setIdentifiers(array(			'TaskManagement\TaskService',
			__CLASS__,
			get_class($this)
		));
		$this->events = $events;
	}
	
	public function getEventManager()
	{
		if (!$this->events) {
			$this->setEventManager(new EventManager());
		}
		return $this->events;
	}
}
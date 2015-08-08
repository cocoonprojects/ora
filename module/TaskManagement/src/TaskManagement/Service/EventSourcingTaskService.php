<?php

namespace TaskManagement\Service;

use Doctrine\ORM\EntityManager;
use People\Entity\Organization;
use Prooph\EventStore\EventStore;
use Prooph\EventStore\Aggregate\AggregateRepository;
use Prooph\EventStore\Aggregate\AggregateType;
use Prooph\EventStore\Stream\SingleStreamStrategy;
use Prooph\EventSourcing\EventStoreIntegration\AggregateTranslator;
use Rhumsaa\Uuid\Uuid;
use TaskManagement\Task;
use TaskManagement\Entity\Task as ReadModelTask;

class EventSourcingTaskService extends AggregateRepository implements TaskService
{
	/**
	 * 
	 * @var EntityManager
	 */
	private $entityManager;

	public function __construct(EventStore $eventStore, EntityManager $entityManager)
	{
		parent::__construct($eventStore, new AggregateTranslator(), new SingleStreamStrategy($eventStore), AggregateType::fromAggregateRootClass(Task::class));
		$this->entityManager = $entityManager;	
	}
	
	public function addTask(Task $task)
	{
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
		return $task;
	}
	
	/**
	 *
	 * @see \TaskManagement\Service\TaskService::findTasks()
	 */
	public function findTasks(Organization $organization) {
		$builder = $this->entityManager->createQueryBuilder ();
		$query = $builder->select ( 't' )
		->from ( ReadModelTask::class, 't' )
		->innerjoin ( 't.stream', 's', 'WITH', 's.organization = :organization')
		->orderBy ( 't.mostRecentEditAt', 'DESC' )
		->setParameter ( ':organization', $organization )
		->getQuery ();
		
		return $query->getResult ();
	}
	
	public function findTask($id) {
		return $this->entityManager->find(ReadModelTask::class, $id);
	}
	
	public function findStreamTasks($streamId) {
		$repository = $this->entityManager->getRepository(ReadModelTask::class)->findBy(array('stream' => $streamId));
		return $repository;
	}
	
	/**
	 * @see \TaskManagement\Service\TaskService::findAcceptedTasksBefore()
	 */
	public function findAcceptedTasksBefore(\DateInterval $interval){
		
		$referenceDate = new \DateTime('now');
		
		$builder = $this->entityManager->createQueryBuilder();
		$query = $builder->select('t')
			->from(ReadModelTask::class, 't')
			->where("DATE_ADD(t.acceptedAt,".$interval->format('%d').", 'DAY') <= :referenceDate") 
			->andWhere('t.status = :taskStatus')
			->setParameter('taskStatus', Task::STATUS_ACCEPTED)
			->setParameter('referenceDate', $referenceDate->format('Y-m-d H:i:s'))
			->getQuery();			
		
		return $query->getResult();		
	}
}

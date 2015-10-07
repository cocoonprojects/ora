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
use TaskManagement\Entity\Stream as ReadModelStream;

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
	 * @param Organization $organization
	 * @param integer $offset
	 * @param integer $limit
	 * @param \DateTime $startOn
	 * @param \DateTime $endOn
	 * @return Task[]
	 */
	public function findTasks(Organization $organization, $offset, $limit, \DateTime $startOn = null, \DateTime $endOn = null)
	{
		$builder = $this->entityManager->createQueryBuilder();
		$query = $builder->select('t')
			->from(ReadModelTask::class, 't')
			->innerjoin('t.stream', 's', 'WITH', 's.organization = :organization')
			->orderBy('t.mostRecentEditAt', 'DESC')
			->setFirstResult($offset)
			->setMaxResults($limit)
			->setParameter(':organization', $organization);

		if($startOn != null){
			$query->andWhere('t.createdAt >= :startOn')
				->setParameter('startOn', $startOn->format("Y-m-d")." 00:00:00");
		}
		if($endOn != null){
			$query->andWhere('t.createdAt <= :endOn')
				->setParameter('endOn', $endOn->format("Y-m-d")." 23:59:59");
		}
		return $query->getQuery()->getResult();
	}
	
	/**
	 * 
	 * @param Organization $organization
	 * @return \Doctrine\ORM\mixed
	 */
	public function countOrganizationTasks(Organization $organization, \DateTime $startOn = null, \DateTime $endOn = null ){
		
		$builder = $this->entityManager->createQueryBuilder();
		$query = $builder->select('count(t)')
			->from(ReadModelTask::class, 't')
			->innerjoin('t.stream', 's', 'WITH', 's.organization = :organization')
			->setParameter(':organization', $organization);
		if($startOn != null){
			$query->andWhere('t.createdAt >= :startOn')
				->setParameter('startOn', $startOn->format("Y-m-d"));
		}
		if($endOn != null){
			$query->andWhere('t.createdAt <= :endOn')
				->setParameter('endOn', $endOn->format("Y-m-d"));
		}
		return intval($query->getQuery()->getSingleScalarResult());
	}
	
	public function findTask($id) {
		return $this->entityManager->find(ReadModelTask::class, $id);
	}
	
	public function findStreamTasks($streamId, $offset, $limit, \DateTime $startOn = null, \DateTime $endOn = null) {
		
		$builder = $this->entityManager->createQueryBuilder();
		$query = $builder->select('t')
			->from(ReadModelTask::class, 't')
			->where('t.stream = :streamId')
			->setParameter(':streamId', $streamId);
		if($startOn != null){
			$query->andWhere('t.createdAt >= :startOn')
			->setParameter('startOn', $startOn->format("Y-m-d"));
		}
		if($endOn != null){
			$query->andWhere('t.createdAt <= :endOn')
			->setParameter('endOn', $endOn->format("Y-m-d"));
		}
		return $query->getQuery()->getResult();
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

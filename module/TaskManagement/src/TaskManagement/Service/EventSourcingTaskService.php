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
use TaskManagement\Entity\TaskMember;
use Doctrine\ORM\Query;

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
	 * @see \TaskManagement\Service\TaskService::findTasks()
	 */
	public function findTasks(Organization $organization, $offset, $limit, $filters)
	{
		$builder = $this->entityManager->createQueryBuilder();
		$query = $builder->select('t')
			->from(ReadModelTask::class, 't')
			->innerjoin('t.stream', 's', 'WITH', 's.organization = :organization')
			->orderBy('t.mostRecentEditAt', 'DESC')
			->setFirstResult($offset)
			->setMaxResults($limit)
			->setParameter(':organization', $organization);

		if(!empty($filters["startOn"])){
			$query->andWhere('t.createdAt >= :startOn')
				->setParameter('startOn', $filters["startOn"]);
		}
		if(!empty($filters["endOn"])){
			$query->andWhere('t.createdAt <= :endOn')
				->setParameter('endOn', $filters["endOn"]);
		}
		if(!empty($filters["memberId"])){
			$query->innerJoin('t.members', 'm', 'WITH', 'm.user = :memberId')
				->setParameter('memberId', $filters["memberId"]);
		}
		if(!empty($filters["memberEmail"])){
			$query->innerJoin('t.members', 'm')
				->innerJoin('m.user', 'u', 'WITH', 'u.email = :memberEmail')
				->setParameter('memberEmail', $filters["memberEmail"]);
		}
		if(is_array($filters)){
			if($filters["status"]>=0){
				$query->andWhere('t.status = :status')->setParameter('status', $filters["status"]);
			}
		}
		return $query->getQuery()->getResult();
	}
	
	/**
	 * @see \TaskManagement\Service\TaskService::countOrganizationTasks()
	 */
	public function countOrganizationTasks(Organization $organization, $filters){
		
		$builder = $this->entityManager->createQueryBuilder();
		$query = $builder->select('count(t)')
			->from(ReadModelTask::class, 't')
			->innerjoin('t.stream', 's', 'WITH', 's.organization = :organization')
			->setParameter(':organization', $organization);

		if(!empty($filters["startOn"])){
			$query->andWhere('t.createdAt >= :startOn')
			->setParameter('startOn', $filters["startOn"]);
		}
		if(!empty($filters["endOn"])){
			$query->andWhere('t.createdAt <= :endOn')
			->setParameter('endOn', $filters["endOn"]);
		}
		if(!empty($filters["memberId"])){
			$query->innerJoin('t.members', 'm', 'WITH', 'm.user = :memberId')
			->setParameter('memberId', $filters["memberId"]);
		}
		if(!empty($filters["memberEmail"])){
			$query->innerJoin('t.members', 'm')
			->innerJoin('m.user', 'u', 'WITH', 'u.email = :memberEmail')
			->setParameter('memberEmail', $filters["memberEmail"]);
		}
		return intval($query->getQuery()->getSingleScalarResult());
	}
	
	public function findTask($id) {
		return $this->entityManager->find(ReadModelTask::class, $id);
	}
	
	/**
	 * @see \TaskManagement\Service\TaskService::findStreamTasks()
	 */
	public function findStreamTasks($streamId, $offset, $limit, $filters){
		
		$builder = $this->entityManager->createQueryBuilder();
		$query = $builder->select('t')
			->from(ReadModelTask::class, 't')
			->where('t.stream = :streamId')
			->setParameter(':streamId', $streamId);

		if(!empty($filters["startOn"])){
			$query->andWhere('t.createdAt >= :startOn')
				->setParameter('startOn', $filters["startOn"]);
		}
		if(!empty($filters["endOn"])){
			$query->andWhere('t.createdAt <= :endOn')
				->setParameter('endOn', $filters["endOn"]);
		}
		if(!empty($filters["memberId"])){
			$query->innerJoin('t.members', 'm', 'WITH', 'm.user = :memberId')
				->setParameter('memberId', $filters["memberId"]);
		}
		if(!empty($filters["memberEmail"])){
			$query->innerJoin('t.members', 'm')
				->innerJoin('m.user', 'u', 'WITH', 'u.email = :memberEmail')
				->setParameter('memberEmail', $filters["memberEmail"]);
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

	/**
	 * @see \TaskManagement\Service\TaskService::findStatsForMember()
	 */
	public function findStatsForMember(Organization $org, $memberId, $filters){
		if(is_null($memberId)){
			return [];
		}

		$builder = $this->entityManager->createQueryBuilder();
		$query = $builder->select('SUM( CASE WHEN m.role=:role THEN 1 ELSE 0 END ) as ownershipsCount')
			->addSelect('COUNT(m.task) as membershipsCount')
			->addSelect('SUM(m.credits) as creditsCount')
			->addSelect('AVG(m.delta) as averageDelta')
			->from(TaskMember::class, 'm')
			->innerJoin('m.task', 't')
			->innerjoin('t.stream', 's', 'WITH', 's.organization = :organization')
			->innerjoin('m.user', 'u', 'WITH', 'u.id = :memberId')
			->setParameter('role', TaskMember::ROLE_OWNER)
			->setParameter('memberId', $memberId)
			->setParameter('organization', $org->getId());

		if(!empty($filters["startOn"])){
			$query->andWhere('t.createdAt >= :startOn')
				->setParameter('startOn', $filters["startOn"]);
		}
		if(!empty($filters["endOn"])){
			$query->andWhere('t.createdAt <= :endOn')
				->setParameter('endOn', $filters["endOn"]);
		}

		return $query->getQuery()->getResult()[0];
	}
}

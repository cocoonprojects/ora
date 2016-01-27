<?php

namespace TaskManagement\Service;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Query;
use People\Organization as WriteModelOrganization;
use People\Entity\Organization;
use Prooph\EventSourcing\EventStoreIntegration\AggregateTranslator;
use Prooph\EventStore\Aggregate\AggregateRepository;
use Prooph\EventStore\Aggregate\AggregateType;
use Prooph\EventStore\EventStore;
use Prooph\EventStore\Stream\SingleStreamStrategy;
use Rhumsaa\Uuid\Uuid;
use TaskManagement\Entity\Task as ReadModelTask;
use TaskManagement\Entity\TaskMember;
use TaskManagement\Task;

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
	 * @param string|\TaskManagement\Service\Uuid $id
	 * @return Task
	 */
	public function getTask($id)
	{
		$tId = $id instanceof Uuid ? $id->toString() : $id;
		$task = $this->getAggregateRoot($tId);
		return $task;
	}

	/**
	 * @see \TaskManagement\Service\TaskService::findTasks()
	 * @param Organization|ReadModelOrganization|String|Uuid $organization
	 * @param int $offset
	 * @param int $limit
	 * @param array $filters
	 * @return \TaskManagement\Task[]
	 */
	public function findTasks($organization, $offset, $limit, $filters)
	{
		switch (get_class($organization)){
			case Organization::class :
			case WriteModelOrganization::class:
				$organizationId = $organization->getId();
				break;
			case Uuid::class:
				$organizationId = $organization->toString();
				break;
			default :
				$organizationId = $organization;
		}
		$builder = $this->entityManager->createQueryBuilder();
		$query = $builder->select('t')
			->from(ReadModelTask::class, 't')
			->innerjoin('t.stream', 's', 'WITH', 's.organization = :organization')
			->orderBy('t.mostRecentEditAt', 'DESC')
			->setFirstResult($offset)
			->setMaxResults($limit)
			->setParameter(':organization', $organizationId);

		if(isset($filters["startOn"])){
			$query->andWhere('t.createdAt >= :startOn')
				->setParameter('startOn', $filters["startOn"]);
		}
		if(isset($filters["endOn"])){
			$query->andWhere('t.createdAt <= :endOn')
				->setParameter('endOn', $filters["endOn"]);
		}
		if(isset($filters['streamId'])) {
			$query->andWhere('t.stream = :streamId')
				->setParameter(':streamId', $filters['streamId']);
		}
		if(isset($filters["memberId"])){
			$query->innerJoin('t.members', 'm', 'WITH', 'm.user = :memberId')
				->setParameter('memberId', $filters["memberId"]);
		}
		if(isset($filters["memberEmail"])){
			$query->innerJoin('t.members', 'm')
				->innerJoin('m.user', 'u', 'WITH', 'u.email = :memberEmail')
				->setParameter('memberEmail', $filters["memberEmail"]);
		}
		if(array_key_exists('status', $filters)){
			$query->andWhere('t.status = :status')->setParameter('status', $filters["status"]);
		}
		return $query->getQuery()->getResult();
	}

	/**
	 * @see \TaskManagement\Service\TaskService::countOrganizationTasks()
	 * @param Organization $organization
	 * @param array $filters
	 * @return int
	 */
	public function countOrganizationTasks(Organization $organization, $filters){
		
		$builder = $this->entityManager->createQueryBuilder();
		$query = $builder->select('count(t)')
			->from(ReadModelTask::class, 't')
			->innerjoin('t.stream', 's', 'WITH', 's.organization = :organization')
			->setParameter(':organization', $organization);

		if(isset($filters["startOn"])){
			$query->andWhere('t.createdAt >= :startOn')
			->setParameter('startOn', $filters["startOn"]);
		}
		if(isset($filters["endOn"])){
			$query->andWhere('t.createdAt <= :endOn')
			->setParameter('endOn', $filters["endOn"]);
		}
		if(isset($filters['streamId'])) {
			$query->andWhere('t.stream = :streamId')
			->setParameter(':streamId', $filters['streamId']);
		}
		if(isset($filters["memberId"])){
			$query->innerJoin('t.members', 'm', 'WITH', 'm.user = :memberId')
			->setParameter('memberId', $filters["memberId"]);
		}
		if(isset($filters["memberEmail"])){
			$query->innerJoin('t.members', 'm')
			->innerJoin('m.user', 'u', 'WITH', 'u.email = :memberEmail')
			->setParameter('memberEmail', $filters["memberEmail"]);
		}
		if(array_key_exists('status', $filters)){
			$query->andWhere('t.status = :status')->setParameter('status', $filters["status"]);
		}
		return intval($query->getQuery()->getSingleScalarResult());
	}
	
	public function findTask($id) {
		return $this->entityManager->find(ReadModelTask::class, $id);
	}

	/**
	 * @see \TaskManagement\Service\TaskService::findAcceptedTasksBefore()
	 * @param \DateInterval $interval
	 * @return ReadModelTask[]
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
	 * @see \TaskManagement\Service\TaskService::findMemberStats()
	 * @param Organization $org
	 * @param string $memberId
	 * @param \DateTime $filters
	 * @return array
	 */
	public function findMemberStats(Organization $org, $memberId, $filters){
		if(is_null($memberId)){
			return [];
		}

		$builder = $this->entityManager->createQueryBuilder();
		$query = $builder->select('COALESCE(SUM( CASE WHEN m.role=:role THEN 1 ELSE 0 END ),0) as ownershipsCount')
			->addSelect('COUNT(m.task) as membershipsCount')
			->addSelect('COALESCE(SUM(m.credits),0) as creditsCount')
			->addSelect('COALESCE(AVG( CASE WHEN t.status >=:taskStatus THEN m.delta ELSE :value END ),0) as averageDelta')
			->from(TaskMember::class, 'm')
			->innerJoin('m.task', 't')
			->innerjoin('t.stream', 's', 'WITH', 's.organization = :organization')
			->innerjoin('m.user', 'u', 'WITH', 'u.id = :memberId')
			->setParameter('role', TaskMember::ROLE_OWNER)
			->setParameter('taskStatus', Task::STATUS_CLOSED)
			->setParameter('value', NULL)
			->setParameter('memberId', $memberId)
			->setParameter('organization', $org->getId());

		if(isset($filters["startOn"])){
			$query->andWhere('t.createdAt >= :startOn')
				->setParameter('startOn', $filters["startOn"]);
		}
		if(isset($filters["endOn"])){
			$query->andWhere('t.createdAt <= :endOn')
				->setParameter('endOn', $filters["endOn"]);
		}

		return $query->getQuery()->getResult()[0];
	}
	/**
	 * (non-PHPdoc)
	 * @see \TaskManagement\Service\TaskService::findItemsBefore()
	 */
	public function findItemsBefore(\DateInterval $interval, $status = null){
		$referenceDate = new \DateTime('now');
		
		$builder = $this->entityManager->createQueryBuilder();
		$query = $builder->select('t')
			->from(ReadModelTask::class, 't')
			->where("DATE_ADD(t.createdAt,".$interval->format('%d').", 'DAY') <= :referenceDate")
			->setParameter('referenceDate', $referenceDate->format('Y-m-d H:i:s'));
		if(!is_null($status)){
			$query->andWhere('t.status = :taskStatus')
				->setParameter('taskStatus', $status);
		}
		return $query->getQuery()->getResult();
	}
}

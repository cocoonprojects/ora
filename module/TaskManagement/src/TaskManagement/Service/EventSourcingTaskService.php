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
use TaskManagement\Task;
use TaskManagement\Entity\Task as ReadModelTask;
use TaskManagement\Entity\TaskMember;
use TaskManagement\Entity\ItemIdeaApproval;

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
	 * @param string|Uuid $id
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
	public function findTasks($organization, $offset, $limit, $filters, $sorting=null)
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

		$orderBy = 't.mostRecentEditAt';
		$orderType = 'DESC';
		if(isset($sorting["orderBy"])) {
			$orderBy = 't.'.$sorting['orderBy'];
		}
		if(isset($sorting["orderType"])) {
			$orderType = $sorting['orderType'];
		}

		$query = $builder->select('t')
			->from(ReadModelTask::class, 't')
			->innerjoin('t.stream', 's', 'WITH', 's.organization = :organization')
			->orderBy($orderBy, $orderType)
			->setFirstResult($offset)
			->setMaxResults($limit)
			->setParameter(':organization', $organizationId);

		$type = 0;
		if(isset($filters["type"]) && $filters["type"]=='decisions') {
			$query->andWhere('t.is_decision = :type')
				->setParameter('type', 1);
		}

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

		$type = 0;
		if(isset($filters["type"]) && $filters["type"]=='decision') {
			$query->andWhere('t.is_decision = :type')
				->setParameter('type', 1);
		}

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
	 * @see \TaskManagement\Service\TaskService::findIdeasCreatedBetween()
	 * @param \DateInterval $after
	 * @param \DateInterval $before
	 * @return ReadModelTask[]
	 */
	public function findIdeasCreatedBetween(\DateInterval $after, \DateInterval $before){
		
		$referenceDate = new \DateTime('now');
		
		$builder = $this->entityManager->createQueryBuilder();
		$query = $builder->select('t')
			->from(ReadModelTask::class, 't')
			->where("DATE_ADD(t.createdAt,".$before->format('%d').", 'DAY') >= :referenceDate") 
			->andWhere("DATE_ADD(t.createdAt,".$after->format('%d').", 'DAY') <= :referenceDate") 
			->andWhere('t.status = :taskStatus')
			->setParameter('taskStatus', Task::STATUS_IDEA)
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
	
	/**
	 * (non-PHPdoc)
	 * @see \TaskManagement\Service\TaskService::countVotesForApproveItem()
	 */
	public function countVotesForItem($itemStatus, $id){
		
		$tId = $id instanceof Uuid ? $id->toString() : $id;
		$builder = $this->entityManager->createQueryBuilder();
	
		$query = $builder->select ( 'COALESCE(SUM( CASE WHEN a.vote.value = 1 THEN 1 ELSE 0 END ),0) as votesFor' )
		->addSelect('COALESCE(SUM( CASE WHEN a.vote.value = 0 THEN 1 ELSE 0 END ),0) as votesAgainst')
		->from(ItemIdeaApproval::class, 'a')
		->innerJoin('a.item', 'item', 'WITH', 'item.status = :status')
		->where('item.id = :id')
		->setParameter ( ':status', $itemStatus)
		->setParameter ( ':id', $tId)
		->getQuery();
		
		return $query->getResult()[0];
	}

}

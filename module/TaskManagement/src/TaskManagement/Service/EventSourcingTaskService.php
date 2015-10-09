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
	 * @param \DateTime | null $startOn
	 * @param \DateTime | null $endOn
	 * @param array $queryOptions
	 * @return Task[]
	 */
	public function findTasks(Organization $organization, $offset, $limit, $queryOptions)
	{
		$builder = $this->entityManager->createQueryBuilder();
		$query = $builder->select('t')
			->from(ReadModelTask::class, 't')
			->innerjoin('t.stream', 's', 'WITH', 's.organization = :organization')
			->orderBy('t.mostRecentEditAt', 'DESC')
			->setFirstResult($offset)
			->setMaxResults($limit)
			->setParameter(':organization', $organization);

		if(is_array($queryOptions)){
			if(isset($queryOptions["startOn"]) && !empty($queryOptions["startOn"])){
				$query->andWhere('t.createdAt >= :startOn')
					->setParameter('startOn', $queryOptions["startOn"]);
			}
			if(isset($queryOptions["endOn"]) && !empty($queryOptions["endOn"])){
				$query->andWhere('t.createdAt <= :endOn')
					->setParameter('endOn', $queryOptions["endOn"]);
			}
			if(isset($queryOptions["memberId"]) && !empty($queryOptions["memberId"])){
				$query->innerJoin('t.members', 'm', 'WITH', 'm.user = :memberId')
					->setParameter('memberId', $queryOptions["memberId"]);
			}
			if(isset($queryOptions["memberEmail"]) && !empty($queryOptions["memberEmail"])){
				$query->innerJoin('t.members', 'm')
					->innerJoin('m.user', 'u', 'WITH', 'u.email = :memberEmail')
					->setParameter('memberEmail', $queryOptions["memberEmail"]);
			}
		}

		return $query->getQuery()->getResult();
	}
	
	/**
	 * 
	 * @param Organization $organization
	 * @param \DateTime | null $startOn
	 * @param \DateTime | null $endOn
	 * @param array $queryOptions
	 * @return \Doctrine\ORM\mixed
	 */
	public function countOrganizationTasks(Organization $organization, $queryOptions){
		
		$builder = $this->entityManager->createQueryBuilder();
		$query = $builder->select('count(t)')
			->from(ReadModelTask::class, 't')
			->innerjoin('t.stream', 's', 'WITH', 's.organization = :organization')
			->setParameter(':organization', $organization);

		if(is_array($queryOptions)){
			if(isset($options["startOn"]) && !empty($queryOptions["startOn"])){
				$query->andWhere('t.createdAt >= :startOn')
				->setParameter('startOn', $queryOptions["startOn"]);
			}
			if(isset($queryOptions["endOn"]) && !empty($queryOptions["endOn"])){
				$query->andWhere('t.createdAt <= :endOn')
				->setParameter('endOn', $queryOptions["endOn"]);
			}
			if(isset($queryOptions["memberId"]) && !empty($queryOptions["memberId"])){
				//$query->addSelect('m.role')
				$query->innerJoin('t.members', 'm', 'WITH', 'm.user = :memberId')
				->setParameter('memberId', $queryOptions["memberId"]);
				//->groupBy("m.role");
			}
			if(isset($queryOptions["memberEmail"]) && !empty($queryOptions["memberEmail"])){
				$query->innerJoin('t.members', 'm')
				->innerJoin('m.user', 'u', 'WITH', 'u.email = :memberEmail')
				->setParameter('memberEmail', $queryOptions["memberEmail"]);
			}
		}
		return intval($query->getQuery()->getSingleScalarResult());
	}
	
	public function findTask($id) {
		return $this->entityManager->find(ReadModelTask::class, $id);
	}
	
	public function findStreamTasks($streamId, $offset, $limit, $queryOptions){
		
		$builder = $this->entityManager->createQueryBuilder();
		$query = $builder->select('t')
			->from(ReadModelTask::class, 't')
			->where('t.stream = :streamId')
			->setParameter(':streamId', $streamId);

		if(is_array($queryOptions)){
			if(isset($queryOptions["startOn"]) && !empty($queryOptions["startOn"])){
				$query->andWhere('t.createdAt >= :startOn')
					->setParameter('startOn', $queryOptions["startOn"]);
			}
			if(isset($queryOptions["endOn"]) && !empty($queryOptions["endOn"])){
				$query->andWhere('t.createdAt <= :endOn')
					->setParameter('endOn', $queryOptions["endOn"]);
			}
			if(isset($queryOptions["memberId"]) && !empty($queryOptions["memberId"])){
				$query->innerJoin('t.members', 'm', 'WITH', 'm.user = :memberId')
					->setParameter('memberId', $queryOptions["memberId"]);
			}
			if(isset($queryOptions["memberEmail"]) && !empty($queryOptions["memberEmail"])){
				$query->innerJoin('t.members', 'm')
					->innerJoin('m.user', 'u', 'WITH', 'u.email = :memberEmail')
					->setParameter('memberEmail', $queryOptions["memberEmail"]);
			}
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

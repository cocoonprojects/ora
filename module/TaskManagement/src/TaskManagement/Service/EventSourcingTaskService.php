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
	 * @param array $filters
	 * @return Task[]
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

		if(is_array($filters)){
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
		}
		return $query->getQuery()->getResult();
	}
	
	/**
	 * 
	 * @param Organization $organization
	 * @param \DateTime | null $startOn
	 * @param \DateTime | null $endOn
	 * @param array $filters
	 * @return \Doctrine\ORM\mixed
	 */
	public function countOrganizationTasks(Organization $organization, $filters){
		
		$builder = $this->entityManager->createQueryBuilder();
		$query = $builder->select('count(t)')
			->from(ReadModelTask::class, 't')
			->innerjoin('t.stream', 's', 'WITH', 's.organization = :organization')
			->setParameter(':organization', $organization);

		if(is_array($filters)){
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
		}
		return intval($query->getQuery()->getSingleScalarResult());
	}
	
	public function findTask($id) {
		return $this->entityManager->find(ReadModelTask::class, $id);
	}
	
	public function findStreamTasks($streamId, $offset, $limit, $filters){
		
		$builder = $this->entityManager->createQueryBuilder();
		$query = $builder->select('t')
			->from(ReadModelTask::class, 't')
			->where('t.stream = :streamId')
			->setParameter(':streamId', $streamId);

		if(is_array($filters)){
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

	public function countTasksOwnership(Organization $org, $memberId, $filters){
		if(is_null($memberId)){
			return 0;
		}

		$builder = $this->entityManager->createQueryBuilder();
		$query = $builder->select('count(t)')
			->from(ReadModelTask::class, 't')
			->innerjoin('t.stream', 's', 'WITH', 's.organization = :organization')
			->innerJoin('t.members', 'm', 'WITH', 'm.user = :memberId')
			->where('m.role = :memberRole')
			->setParameter('organization', $org)
			->setParameter('memberId', $memberId)
			->setParameter('memberRole', TaskMember::ROLE_OWNER);

		if(is_array($filters)){
			if(!empty($filters["startOn"])){
				$query->andWhere('t.createdAt >= :startOn')
				->setParameter('startOn', $filters["startOn"]);
			}
			if(!empty($filters["endOn"])){
				$query->andWhere('t.createdAt <= :endOn')
				->setParameter('endOn', $filters["endOn"]);
			}
		}
		return intval($query->getQuery()->getSingleScalarResult());
	}

	public function findTaskMemberInClosedTasks(Organization $org, $memberId, $filters){
		if(is_null($memberId)){
			return [];
		}

		$builder = $this->entityManager->createQueryBuilder();
		$query = $builder->select('m')
			->from(TaskMember::class, 'm')
			->innerJoin('m.task', 't')
			->innerjoin('t.stream', 's', 'WITH', 's.organization = :organization')
			->innerjoin('m.user', 'u', 'WITH', 'u.id = :memberId')
			->where('t.status = :taskStatus')
			->setParameter('organization', $org)
			->setParameter('memberId', $memberId)
			->setParameter('taskStatus', Task::STATUS_CLOSED);

		if(is_array($filters)){
			if(!empty($filters["startOn"])){
				$query->andWhere('t.createdAt >= :startOn')
				->setParameter('startOn', $filters["startOn"]);
			}
			if(!empty($filters["endOn"])){
				$query->andWhere('t.createdAt <= :endOn')
				->setParameter('endOn', $filters["endOn"]);
			}
		}
		return $query->getQuery()->getResult();
	}
}

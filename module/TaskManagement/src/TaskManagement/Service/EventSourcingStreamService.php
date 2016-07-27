<?php

namespace TaskManagement\Service;

use Application\Entity\User;
use Doctrine\ORM\EntityManager;
use People\Entity\Organization as ReadModelOrganization;
use People\Organization;
use Prooph\EventSourcing\EventStoreIntegration\AggregateTranslator;
use Prooph\EventStore\Aggregate\AggregateRepository;
use Prooph\EventStore\Aggregate\AggregateType;
use Prooph\EventStore\EventStore;
use Prooph\EventStore\Stream\SingleStreamStrategy;
use Rhumsaa\Uuid\Uuid;
use TaskManagement\Entity\Stream as ReadModelStream;
use TaskManagement\Stream;
use Kanbanize\KanbanizeStream;

/**
 * @author Giannotti Fabio
 */
class EventSourcingStreamService extends AggregateRepository implements StreamService
{
	/**
	 *
	 * @var EntityManager
	 */
	private $entityManager;
	
	public function __construct(EventStore $eventStore, EntityManager $entityManager)
	{
		parent::__construct($eventStore, new AggregateTranslator(), new SingleStreamStrategy($eventStore), AggregateType::fromAggregateRootClass(Stream::class));
		$this->entityManager = $entityManager;
	}

	public function addStream(Stream $stream)
	{
		$this->addAggregateRoot($stream);
		return $stream;
	}

	public function addKanbanizeStream(KanbanizeStream $stream)
	{
		$this->addAggregateRoot($stream);
		return $stream;
	}

	public function createStream(Organization $organization, $subject, User $createdBy)
	{
		$this->eventStore->beginTransaction();
		try {
			$rv = Stream::create($organization, $subject, $createdBy);
			$this->addAggregateRoot($rv);
			$this->eventStore->commit();
		} catch (\Exception $e) {
			$this->eventStore->rollback();
			throw $e;
		}
		return $rv;
	}

	public function createKanbanizeStream(Organization $organization, $subject, User $createdBy)
	{
		$this->eventStore->beginTransaction();
		try {
			$rv = KanbanizeStream::create($organization, $subject, $createdBy);
			$this->addAggregateRoot($rv);
			$this->eventStore->commit();
		} catch (\Exception $e) {
			$this->eventStore->rollback();
			throw $e;
		}
		return $rv;
	}

	/**
	 * @param string $id
	 * @return null|Stream
	 */
	public function getStream($id)
	{
		$sId = $id instanceof Uuid ? $id->toString() : $id;
		return $this->getAggregateRoot($sId);
	}

	/**
	 * @param string $id
	 * @return null|ReadModelStream
	 * @throws \Doctrine\ORM\ORMException
	 * @throws \Doctrine\ORM\OptimisticLockException
	 * @throws \Doctrine\ORM\TransactionRequiredException
	 */
	public function findStream($id)
	{
		return $this->entityManager->find(ReadModelStream::class, $id);
	}

	/**
	 * @param ReadModelOrganization $organization
	 * @return ReadModelStream[]
	 */
	public function findStreams(ReadModelOrganization $organization) {
		$builder = $this->entityManager->createQueryBuilder();
		
		$query = $builder->select ( 's' )
			->from(ReadModelStream::class, 's')
			->where('s.organization = :organization')
			->setParameter ( ':organization', $organization )
			->getQuery();
		
		return $query->getResult();
	}
} 
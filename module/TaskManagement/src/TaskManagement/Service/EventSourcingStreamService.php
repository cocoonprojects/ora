<?php

namespace TaskManagement\Service;

use Prooph\EventStore\EventStore;
use Prooph\EventStore\Aggregate\AggregateRepository;
use Prooph\EventStore\Aggregate\AggregateType;
use Prooph\EventStore\Stream\SingleStreamStrategy;
use Prooph\EventSourcing\EventStoreIntegration\AggregateTranslator;
use Doctrine\ORM\EntityManager;
use Rhumsaa\Uuid\Uuid;
use Application\Entity\User;
use People\Organization;
use People\Entity\OrganizationMembership;
use TaskManagement\Stream;
use TaskManagement\Entity\Stream as ReadModelStream;

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

	public function getStream($id)
	{
		$sId = $id instanceof Uuid ? $id->toString() : $id;
		return $this->getAggregateRoot($sId);
	}
		
	public function findStream($id)
	{
		return $this->entityManager->find(ReadModelStream::class, $id);
	}
	
	public function findStreams(User $user) {
		$builder = $this->entityManager->createQueryBuilder();
		$query = $builder->select('s')
			->from(ReadModelStream::class, 's')
			->leftJoin(OrganizationMembership::class, 'm', 'WITH', 'm.organization = s.organization')
			->where('m.member = :user')
			->setParameter('user', $user)
			->getQuery();
		return $query->getResult();
	}
} 
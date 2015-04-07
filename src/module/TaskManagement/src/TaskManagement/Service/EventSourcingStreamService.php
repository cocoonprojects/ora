<?php

namespace TaskManagement\Service;

use Prooph\EventStore\EventStore;
use Prooph\EventStore\Aggregate\AggregateRepository;
use Prooph\EventStore\Aggregate\AggregateType;
use Prooph\EventStore\Stream\SingleStreamStrategy;
use Prooph\EventSourcing\EventStoreIntegration\AggregateTranslator;
use Doctrine\ORM\EntityManager;
use Rhumsaa\Uuid\Uuid;
use Ora\User\User;
use Application\Organization;
use TaskManagement\Stream;

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
		return $this->entityManager->find('Ora\ReadModel\Stream', $id);
	}
	
	public function findStreams(User $user) {
		$builder = $this->entityManager->createQueryBuilder();
		$query = $builder->select('s')
			->from('Ora\ReadModel\Stream', 's')
			->leftJoin('Ora\ReadModel\OrganizationMembership', 'm', 'WITH', 'm.organization = s.organization')
			->where('m.member = :user')
			->setParameter('user', $user)
			->getQuery();
		return $query->getResult();
	}
} 
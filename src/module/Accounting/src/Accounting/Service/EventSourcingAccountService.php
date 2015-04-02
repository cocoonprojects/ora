<?php

namespace Accounting\Service;

use Prooph\EventStore\EventStore;
use Prooph\EventStore\Aggregate\AggregateRepository;
use Prooph\EventStore\Aggregate\AggregateType;
use Prooph\EventStore\Stream\MappedSuperclassStreamStrategy;
use Prooph\EventSourcing\EventStoreIntegration\AggregateTranslator;
use Doctrine\ORM\EntityManager;
use Rhumsaa\Uuid\Uuid;
use Ora\User\User;
use Application\Organization;
use Accounting\Account;
use Accounting\OrganizationAccount;

class EventSourcingAccountService extends AggregateRepository implements AccountService
{
	private $aggregateRootType;
	/**
	 * 
	 * @var EntityManager
	 */
	private $entityManager;
	
	public function __construct(EventStore $eventStore, EntityManager $entityManager) {
		$this->aggregateRootType = new AggregateType('Accounting\Account');
		parent::__construct($eventStore, new AggregateTranslator(), new MappedSuperclassStreamStrategy($eventStore, $this->aggregateRootType, [$this->aggregateRootType->toString() => 'event_stream']));
		$this->entityManager = $entityManager;
	}
	
	public function createPersonalAccount(User $holder) {
		$this->eventStore->beginTransaction();
// 		try {
			$account = Account::create($holder);
			$this->addAggregateRoot($account);
			$this->eventStore->commit();
// 		} catch (\Exception $e) {
// 			$this->eventStore->rollback();
// 			throw $e;
// 		}
		return $account;
	}
	
	public function createOrganizationAccount(Organization $organization, User $holder) {
		$this->eventStore->beginTransaction();
		try {
			$account = OrganizationAccount::createOrganizationAccount($organization, $holder);
			$this->addAggregateRoot($account);
			$this->eventStore->commit();
		} catch (\Exception $e) {
			$this->eventStore->rollback();
			throw $e;
		}
		return $account;
	}
	
	public function getAccount($id) {
		$aId = $id instanceof Uuid ? $id->toString() : $id;
		try {
			$rv = $this->getAggregateRoot($this->aggregateRootType, $aId);
			return $rv;
		} catch (\RuntimeException $e) {
			return null;
		}
	}
	
	public function findAccounts(User $holder) {
		$builder = $this->entityManager->createQueryBuilder();
		$query = $builder->select('a')
			->from('Ora\ReadModel\Account', 'a')
			->leftJoin('Ora\ReadModel\OrganizationMembership', 'm', 'WITH', 'm.organization = a.organization')
			->where($builder->expr()->orX(':user = m.member', ':user MEMBER OF a.holders'))
			->setParameter('user', $holder)
			->getQuery();
		return $query->getResult();
	}
	
	public function findAccount($id) {
		return $this->entityManager->getRepository('Ora\ReadModel\Account')->find($id);
	}
	
	public function findPersonalAccount(User $user) {
		$builder = $this->entityManager->createQueryBuilder();
		$query = $builder->select('a')
			->from('Ora\ReadModel\Account', 'a')
			->where($builder->expr()->andX(':user MEMBER OF a.holders', 'a.organization IS NULL'))
			->setParameter('user', $user)
			->getQuery();
		return $query->getSingleResult();
	}
}
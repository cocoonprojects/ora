<?php

namespace Accounting\Service;

use Prooph\EventStore\EventStore;
use Prooph\EventStore\Aggregate\AggregateRepository;
use Prooph\EventStore\Aggregate\AggregateType;
use Prooph\EventStore\Stream\SingleStreamStrategy;
use Prooph\EventSourcing\EventStoreIntegration\AggregateTranslator;
use Doctrine\ORM\EntityManager;
use Rhumsaa\Uuid\Uuid;
use Application\Entity\User;
use Application\Organization;
use Application\Entity\OrganizationMembership;
use Accounting\Account;
use Accounting\OrganizationAccount;
use Accounting\Entity\Account as ReadModelAccount;
use Accounting\Entity\OrganizationAccount as ReadModelOrgAccount;

class EventSourcingAccountService extends AggregateRepository implements AccountService
{
	/**
	 * 
	 * @var EntityManager
	 */
	private $entityManager;
	
	public function __construct(EventStore $eventStore, EntityManager $entityManager) {
		parent::__construct($eventStore, new AggregateTranslator(), new SingleStreamStrategy($eventStore), AggregateType::fromAggregateRootClass(Account::class));
		$this->entityManager = $entityManager;
	}
	
	public function createPersonalAccount(User $holder) {
		$this->eventStore->beginTransaction();
		try {
			$account = Account::create($holder);
			$this->addAggregateRoot($account);
			$this->eventStore->commit();
		} catch (\Exception $e) {
			$this->eventStore->rollback();
			throw $e;
		}
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
		return $this->getAggregateRoot($aId);
	}
	
	public function findAccounts(User $holder) {
		$builder = $this->entityManager->createQueryBuilder();
		$query = $builder->select('a')
			->from(ReadModelAccount::class, 'a')
			->leftJoin(OrganizationMembership::class, 'm', 'WITH', 'm.organization = a.organization')
			->where($builder->expr()->orX(':user = m.member', ':user MEMBER OF a.holders'))
			->setParameter('user', $holder)
			->getQuery();
		return $query->getResult();
	}
	
	public function findAccount($id) {
		return $this->entityManager->getRepository(ReadModelAccount::class)->find($id);
	}
	
	public function findPersonalAccount(User $user) {
		$builder = $this->entityManager->createQueryBuilder();
		$query = $builder->select('a')
			->from(ReadModelAccount::class, 'a')
			->where($builder->expr()->andX(':user MEMBER OF a.holders', 'a.organization IS NULL'))
			->setParameter('user', $user)
			->getQuery();
		return $query->getSingleResult();
	}
	
	public function findOrganizationAccount($organizationId) {
		$oId = $organizationId instanceof Uuid ? $organizationId->toString() : $organizationId;
		return $this->entityManager->getRepository(ReadModelOrgAccount::class)->findOneBy(array('organization' => $oId));
	}
}
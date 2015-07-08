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
use People\Organization;
use People\Entity\OrganizationMembership;
use Accounting\Account;
use Accounting\OrganizationAccount;
use Accounting\Entity\Account as ReadModelAccount;
use Accounting\Entity\OrganizationAccount as ReadModelOrgAccount;
use Zend\Db\TableGateway\Exception\RuntimeException;

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

	/**
	 * @param User $holder
	 * @param Organization $organization
	 * @return Account
	 * @throws \Exception
	 */
	public function createPersonalAccount(User $holder, Organization $organization) {
		$this->eventStore->beginTransaction();
		try {
			$account = Account::create($organization, $holder);
			$this->addAggregateRoot($account);
			$this->eventStore->commit();
		} catch (\Exception $e) {
			$this->eventStore->rollback();
			throw $e;
		}
		return $account;
	}

	/**
	 * @param Organization $organization
	 * @param User $holder
	 * @return Account
	 * @throws \Exception
	 */
	public function createOrganizationAccount(Organization $organization, User $holder) {
		$this->eventStore->beginTransaction();
		try {
			$account = OrganizationAccount::create($organization, $holder);
			$this->addAggregateRoot($account);
			$this->eventStore->commit();
		} catch (\Exception $e) {
			try {
				$this->eventStore->rollback();
			} catch (RuntimeException $e1) {
				// da loggare
			}
			throw $e;
		}
		return $account;
	}

	/**
	 * @param string|Uuid
	 * @return null|object
	 */
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

	/**
	 * @param string
	 * @return null|object
	 */
	public function findAccount($id) {
		return $this->entityManager->getRepository(ReadModelAccount::class)->find($id);
	}

	/**
	 * @param User|string $user
	 * @param Organization|string $organization
	 * @return Account
	 * @throws \Doctrine\ORM\NoResultException
	 * @throws \Doctrine\ORM\NonUniqueResultException
	 */
	public function findPersonalAccount($user, $organization) {
		$builder = $this->entityManager->createQueryBuilder();
		$query = $builder->select('a')
			->from(ReadModelAccount::class, 'a')
			->where($builder->expr()->andX(':user MEMBER OF a.holders', 'a.organization = :organization'))
			->setParameter('user', $user)
			->setParameter('organization', $organization)
			->getQuery();
		return $query->getSingleResult();
	}

	/**
	 * @param Organization|string $organization
	 * @return null|object
	 */
	public function findOrganizationAccount($organization) {
		return $this->entityManager->getRepository(ReadModelOrgAccount::class)->findOneBy(array('organization' => $organization));
	}
}
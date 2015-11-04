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
use People\Entity\Organization as ReadModelOrganization;
use Accounting\Account;
use Accounting\OrganizationAccount;
use Accounting\Entity\Account as ReadModelAccount;
use Accounting\Entity\OrganizationAccount as ReadModelOrgAccount;
use Zend\Db\TableGateway\Exception\RuntimeException;
use Accounting\Entity\PersonalAccount;
use Accounting\Entity\Transaction;

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
	 * @codeCoverageIgnore
	 */
	public function getAccount($id) {
		$aId = $id instanceof Uuid ? $id->toString() : $id;
		return $this->getAggregateRoot($aId);
	}
	
	public function findAccounts(User $holder, ReadModelOrganization $organization) {
		$builder = $this->entityManager->createQueryBuilder();
		$query = $builder->select('a')
			->from(ReadModelAccount::class, 'a')
			->leftJoin(OrganizationMembership::class, 'm', 'WITH', 'm.organization = a.organization')
			->where($builder->expr()->orX(':user = m.member', ':user MEMBER OF a.holders'))
			->andWhere('a.organization = :organization')
			->setParameter('user', $holder)
			->setParameter('organization', $organization)
			->getQuery();
		return $query->getResult();
	}

	/**
	 * @param string
	 * @return null|object
	 * @codeCoverageIgnore
	 */
	public function findAccount($id) {
		return $this->entityManager->getRepository(ReadModelAccount::class)->find($id);
	}

	/**
	 * Personal Account could be more than one in case of a join->unjoin->join of an organization. We do not remove old
	 * account but we make it unacessible
	 *
	 * @param User|string $user
	 * @param Organization|string $organization
	 * @return Account
	 * @throws \Doctrine\ORM\NoResultException
	 * @throws \Doctrine\ORM\NonUniqueResultException
	 * @codeCoverageIgnore
	 */
	public function findPersonalAccount($user, $organization) {
		$builder = $this->entityManager->createQueryBuilder();
		$query = $builder->select('p')
			->from(PersonalAccount::class, 'p')
			->where($builder->expr()->andX(':user MEMBER OF p.holders', 'p.organization = :organization'))
			->orderBy('p.createdAt', 'DESC')
			->setParameter('user', $user)
			->setParameter('organization', $organization)
			->getQuery();
		$query->setMaxResults(1);
		
		return $query->getSingleResult();
	}

	/**
	 * @param Organization|string $organization
	 * @return null|object
	 * @codeCoverageIgnore
	 */
	public function findOrganizationAccount($organization) {
		return $this->entityManager->getRepository(ReadModelOrgAccount::class)->findOneBy(array('organization' => $organization));
	}

	/**
	 * (non-PHPdoc)
	 * @see \Accounting\Service\AccountService::findTransactions()
	 */
	public function findTransactions(ReadModelAccount $account, $limit, $offset){
		$builder = $this->entityManager->createQueryBuilder();
		$query = $builder->select('t')
			->from(Transaction::class, 't')
			->where($builder->expr()->orX(':account = t.payee AND t.amount > 0', ':account = t.payer  AND t.amount < 0'))
			->setMaxResults($limit)
			->setFirstResult($offset)
			->setParameter('account', $account)
			->addOrderBy('t.createdAt', 'DESC')
			->addOrderBy('t.id', 'DESC')
			->getQuery();
		return $query->getResult();
	}

	/**
	 * (non-PHPdoc)
	 * @see \Accounting\Service\AccountService::countTransactions()
	 */
	public function countTransactions(ReadModelAccount $account){
		$builder = $this->entityManager->createQueryBuilder();
		$query = $builder->select('count(t)')
			->from(Transaction::class, 't')
			->where($builder->expr()->orX(':account = t.payee AND t.amount > 0', ':account = t.payer  AND t.amount < 0'))
			->setParameter('account', $account)
			->getQuery();
		return intval($query->getSingleScalarResult());
	}

	/**
	 * (non-PHPdoc)
	 * @see \Accounting\Service\AccountService::transfer()
	 */
	public function transfer(Account $payer,Account $payee, $amount, $description, User $by){
		$this->eventStore->beginTransaction();
		try {
			$payer->transferOut(-$amount, $payee, $description, $by);
			$payee->transferIn($amount, $payer, $description, $by);
			$this->eventStore->commit();
		}catch (\Exception $e) {
			$this->eventStore->rollback();
			throw $e;
		}
	}
}
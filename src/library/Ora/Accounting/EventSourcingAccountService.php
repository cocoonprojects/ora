<?php
namespace Ora\Accounting;

use \DateTime;
use Prooph\EventStore\EventStore;
use Prooph\EventStore\Stream\StreamStrategyInterface;
use Prooph\EventStore\Aggregate\AggregateRepository;
use Prooph\EventStore\Aggregate\AggregateType;
use Prooph\EventSourcing\EventStoreIntegration\AggregateTranslator;
use Rhumsaa\Uuid\Uuid;
use Ora\User\User;
use Doctrine\ORM\EntityManager;
use Ora\Organization\Organization;
use Prooph\EventStore\Stream\MappedSuperclassStreamStrategy;

class EventSourcingAccountService extends AggregateRepository implements AccountService
{
	private $aggregateRootType;
	/**
	 * 
	 * @var EntityManager
	 */
	private $entityManager;
	
	public function __construct(EventStore $eventStore, EntityManager $entityManager) {
		$this->aggregateRootType = new AggregateType('Ora\Accounting\Account');
		parent::__construct($eventStore, new AggregateTranslator(), new MappedSuperclassStreamStrategy($eventStore, $this->aggregateRootType, [$this->aggregateRootType->toString() => 'event_stream']));
		$this->entityManager = $entityManager;
	}
	
	public function createPersonalAccount(User $holder) {
		$this->eventStore->beginTransaction();
		$account = Account::create($holder);
		$this->addAggregateRoot($account);
		$this->eventStore->commit();
		return $account;
	}
	
	public function createOrganizationAccount(User $holder, Organization $organization) {
		$this->eventStore->beginTransaction();
		$account = OrganizationAccount::createOrganizationAccount($holder, $organization);
		$this->addAggregateRoot($account);
		$this->eventStore->commit();
		return $account;
	}
	
	public function getAccount($id) {
		return $this->getAggregateRoot($this->aggregateRootType, $id);
	}
	
	public function deposit(CreditsAccount $destination, $value) {
		$e = new CreditsDepositedEvent($when, $this, $value);
		$this->eventStore->appendToStream($e);
	}
	
	public function withdraw(CreditsAccount $source, $value) {
		$e = new CreditsWithdrawnEvent($when, $this, $value);
		$this->eventStore->appendToStream($e);
	}
	
	public function transfer(CreditsAccount $source, CreditsAccount $destination, $value, \DateTime $when) {
		try {
			$source->withdraw($value, $when);
			$destination->deposit($value, $when);
		} catch (Exception $e) {
			
		}
	}

	public function findAccounts(User $holder) {
		$builder = $this->entityManager->createQueryBuilder();
		$query = $builder->select('a')
			->from('Ora\ReadModel\Account', 'a')
			->where(':user MEMBER OF a.holders')
			->setParameter('user', $holder)
			->getQuery();
		return $query->getResult();
	}
	
}
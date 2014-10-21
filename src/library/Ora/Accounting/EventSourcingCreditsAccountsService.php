<?php
namespace Ora\Accounting;

use \DateTime;
use Prooph\EventStore\EventStore;
use Prooph\EventStore\Stream\StreamStrategyInterface;
use Prooph\EventStore\Aggregate\AggregateRepository;
use Prooph\EventStore\Aggregate\AggregateType;
use Prooph\EventSourcing\EventStoreIntegration\AggregateTranslator;
use Rhumsaa\Uuid\Uuid;

class EventSourcingCreditsAccountsService extends AggregateRepository implements CreditsAccountsService {
	
	public function __construct(EventStore $eventStore, StreamStrategyInterface $eventStoreStrategy) {
		parent::__construct($eventStore, new AggregateTranslator(), $eventStoreStrategy, new AggregateType('Ora\Accounting\CreditsAccount'));
	}
	
	public function create() {
		$createdAt = new \DateTime();
		$this->eventStore->beginTransaction();
		$account = CreditsAccount::create($createdAt);
		$this->addAggregateRoot($account);
		$this->eventStore->commit();
	}
	
	public function getAccount($id) {
		return $this->getAggregateRoot($this->aggregateType, $id);
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

	public function listAccounts() {
		//$this->getAggregateType(self::$creditsAccountType);
		$a = new CreditsAccount('123458', new \DateTime(), $this->eventStore, 'CUN');
		$a->setBalance(new Balance(1500, new \DateTime()));
		$b = new CreditsAccount('200060', new \DateTime(), $this->eventStore, 'CUN');
		$b->setBalance(new Balance(1500, new \DateTime()));
		return array($a, $b);
	}
	
}
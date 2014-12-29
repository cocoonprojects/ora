<?php
namespace Ora\Accounting;

use Rhumsaa\Uuid\Uuid;
use Ora\DomainEntity;
use Ora\User\User;

/**
 * 
 * @author andreabandera
 *
 */
class Account extends DomainEntity {
	
	/**
	 * 
	 * @var Balance
	 */
	private $balance;
	
	private $holders = array();
	
	public static function create(User $holder) {
		$rv = new self();
		$rv->id = Uuid::uuid4();
		$rv->holders[$holder->getId()] = $holder->getFirstname().' '.$holder->getLastname();
		// At creation time the balance is 0
		$rv->recordThat(AccountCreated::occur($rv->id->toString(), array(
				'balance' => 0,
				'holders' => $rv->holders,
		)));
		return $rv;
	}
	
	public function deposit($amount, User $holder, $description) {
		if ($amount <= 0) {
			throw new IllegalAmountException($amount);
		}
// 		if(!array_key_exists($holder->getId(), $this->holders)) {
// 			throw new IllegalPayerException();
// 		}
		$this->recordThat(CreditsDeposited::occur($this->id->toString(), array(
				'amount' => $amount,
				'description' => $description,
				'payer'	 => $holder->getId(),
		)));
	}
	
	public function getBalance() {
		return $this->balance;
	}
	
	protected function whenAccountCreated(AccountCreated $event) {
		$this->id = Uuid::fromString($event->aggregateId());
		$this->balance = new Balance(0, $event->occurredOn());
		$this->holders = $event->payload()['holders'];
	}
	
	protected function whenCreditsDeposited(CreditsDeposited $e) {
		$current = $this->getBalance()->getValue();
		$value = $e->payload()['amount'];
		$this->balance = new Balance($current + $value, $e->occurredOn());
	}
	
}
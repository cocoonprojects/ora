<?php
namespace Accounting;

use Rhumsaa\Uuid\Uuid;
use Ora\DomainEntity;
use Application\Entity\User;
use Ora\DuplicatedDomainEntityException;

class Account extends DomainEntity {
	
	/**
	 * 
	 * @var Balance
	 */
	private $balance;
	
	private $holders = array();
	
	public static function create(User $createdBy) {
		$rv = new self();
		// At creation time the balance is 0
		$rv->recordThat(AccountCreated::occur(Uuid::uuid4()->toString(), array(
				'balance' => 0,
				'by' => $createdBy->getId()
		)));
		$rv->addHolder($createdBy, $createdBy);
		return $rv;
	}
	
	public function getBalance() {
		return $this->balance;
	}
	
	public function getHolders() {
		return $this->holders;
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
				'balance' => $this->balance->getValue() + $amount,
				'prevBalance' => $this->balance->getValue(),
		)));
		return $this;
	}
	
	public function transferIn($amount, Account $payer, $description, User $by) {
		if ($amount <= 0) {
			throw new IllegalAmountException($amount);
		}
		$this->recordThat(IncomingCreditsTransferred::occur($this->id->toString(), array(
				'amount' => $amount,
				'description' => $description,
				'payer'	 => $payer->getId()->toString(),
				'balance' => $this->balance->getValue() + $amount,
				'prevBalance' => $this->balance->getValue(),
				'by' => $by->getId(),
		)));
		return $this;
	}
	
	public function transferOut($amount, Account $payee, $description, User $by) {
		if ($amount >= 0) {
			throw new IllegalAmountException($amount);
		}
		$this->recordThat(OutgoingCreditsTransferred::occur($this->id->toString(), array(
				'amount' => $amount,
				'description' => $description,
				'payee'	 => $payee->getId()->toString(),
				'balance' => $this->balance->getValue() + $amount,
				'prevBalance' => $this->balance->getValue(),
				'by' => $by->getId(),
		)));
		return $this;
	}
	
	public function addHolder(User $holder, User $by) {
		if(array_key_exists($holder->getId(), $this->holders)) {
			throw new DuplicatedDomainEntityException($this, $user);
		}
		$this->recordThat(HolderAdded::occur($this->id->toString(), array(
				'id' => $holder->getId(),
				'firstname' => $holder->getFirstname(),
				'lastname' => $holder->getLastname(),
				'by' => $by->getId(),
		)));
		return $this;
	}
	
	protected function whenAccountCreated(AccountCreated $event) {
		$this->id = Uuid::fromString($event->aggregateId());
		$this->balance = new Balance(0, $event->occurredOn());
	}
	
	protected function whenHolderAdded(HolderAdded $e) {
		$id = $e->payload()['id'];
		$firstname = $e->payload()['firstname'];
		$lastname = $e->payload()['lastname'];
		$this->holders[$id] = $firstname.' '.$lastname;
	}
	
	protected function whenCreditsDeposited(CreditsDeposited $e) {
		$current = $this->getBalance()->getValue();
		$value = $e->payload()['amount'];
		$this->balance = new Balance($current + $value, $e->occurredOn());
	}
	
	protected function whenIncomingCreditsTransferred(IncomingCreditsTransferred $e) {
		$current = $this->getBalance()->getValue();
		$value = $e->payload()['amount'];
		$this->balance = new Balance($current + $value, $e->occurredOn());
	}
	
	protected function whenOutgoingCreditsTransferred(OutgoingCreditsTransferred $e) {
		$current = $this->getBalance()->getValue();
		$value = $e->payload()['amount'];
		$this->balance = new Balance($current + $value, $e->occurredOn());
	}
	
}
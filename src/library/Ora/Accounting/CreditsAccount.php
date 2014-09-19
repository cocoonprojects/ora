<?php
namespace Ora\Accounting;

use Doctrine\ORM\Mapping AS ORM;
use Ora\DomainEntity;

/**
 * @ORM\Entity @ORM\Table(name="creditsAccounts")
 * @author andreabandera
 *
 */
class CreditsAccount extends DomainEntity {
	
	/**
	 * @ORM\Column(type="string")
	 * @var string
	 */
	private $currency;
	
	/**
	 * ORM\Embedded(class="Balance")
	 * @var Balance
	 */
	private $balance;
	
	public function __construct($id, $createdAt, $es, $currency) {
		parent::__construct($id, $createdAt, $es);
		$this->currency = $currency;
		// At creation time the balance is 0
		$this->balance = new Balance(0, $createdAt);
	}
	
	public function getCurrency() {
		return $this->currency;
	}
	
	public function setBalance(Balance $balance) {
		$this->balance = $balance;
	}
	
	public function getBalance() {
		return $this->balance;
	}
	
	public function deposit($value, $currency, \DateTime $when) {
		if($currency != $this->currency) {
			throw new UnsupportedChangeException($currency, $this->currency);
		}
		$e = new CreditsDepositedEvent($when, $this, $value);
		$this->appendToStream($e);
	}
	
	public function withdraw($value, $currency, \DateTime $when) {
		if($currency != $this->currency) {
			throw new UnsupportedChangeException($currency, $this->currency);
		}
		$e = new CreditsWithdrawnEvent($when, $this, $value);
		$this->appendToStream($e);
	}
	
	private function applyCreditsDepositedEvent(CreditsDepositedEvent $e) {
		$current = $this->getBalance()->getValue();
		$updated = new Balance($current + $e->getValue(), $e->getFiredAt());
		$this->setBalance($updated);
	}
	
}
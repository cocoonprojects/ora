<?php
namespace Ora\Accounting;

use Doctrine\ORM\Mapping AS ORM;
use Ora\DomainEntity;
use Rhumsaa\Uuid\Uuid;

/**
 * ORM\Entity @ORM\Table(name="creditsAccounts")
 * @author andreabandera
 *
 */
class CreditsAccount extends DomainEntity {
	
	/**
	 * ORM\Embedded(class="Balance")
	 * @var Balance
	 */
	private $balance;
	
	public static function create(\DateTime $createdAt = null) {
		$d = $createdAt == null ? new \DateTime() : $createdAt;
		$d = $d->format('Y-m-d H:i:s');
		$id = Uuid::uuid4();
		
		$rv = new self();
		// At creation time the balance is 0
		$rv->recordThat(CreditsAccountCreatedEvent::occur($id->toString(), array(
							'createdAt' => $d,
							'balanceValue' => 0,
							'balanceDate' => $d
						)
				)
		);
		
		return $rv;
	}
	
	public function setBalance(Balance $balance) {
		$this->balance = $balance;
	}
	
	public function getBalance() {
		return $this->balance;
	}
	
	private function whenCreditsDepositedEvent(CreditsDepositedEvent $e) {
		$current = $this->getBalance()->getValue();
		$updated = new Balance($current + $e->getValue(), $e->getFiredAt());
		$this->setBalance($updated);
	}
	
	protected function whenCreditsAccountCreatedEvent(CreditsAccountCreatedEvent $event) {
		$this->id = Uuid::fromString($event->aggregateId());
		$this->createdAt = $event->getCreatedAt();
		$this->setBalance($event->getBalance());
	}
}
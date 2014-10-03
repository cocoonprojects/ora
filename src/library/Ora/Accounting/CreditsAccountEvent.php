<?php
namespace Ora\Accounting;

use Doctrine\ORM\Mapping AS ORM;
use Ora\DomainEvent;
use \DateTime;

/**
 * @ORM\Entity
 * @author andreabandera
 *
 */
class CreditsAccountEvent extends DomainEvent {

	protected $account;

	protected function __construct(DateTime $firedAt, CreditsAccount $account) {
		parent::__construct($firedAt);
		$this->account = $account;
		$this->aggregateId = $account->getId();
	}

}
<?php
namespace Ora\Accounting;

use Doctrine\ORM\Mapping AS ORM;
use \DateTime;

/**
 * @ORM\Entity
 * @author andreabandera
 *
 */
final class CreditsAccountCreatedEvent extends CreditsAccountEvent {
	
	private $account;
	
	public function __construct(DateTime $firedAt, CreditsAccount $account) {
		parent::__construct($firedAt, $account);
	}
	
}
<?php
namespace Ora\Accounting;

use Doctrine\ORM\Mapping AS ORM;
use \DateTime;

/**
 * @ORM\Entity
 * @author andreabandera
 *
 */
class CreditsAccountCreatedEvent extends CreditsAccountEvent {
	
	public function getCreatedAt() {
		$d = $this->toPayloadReader()->stringValue('createdAt');
		return date_create_from_format('Y-m-d H:i:s', $d);
	}

}
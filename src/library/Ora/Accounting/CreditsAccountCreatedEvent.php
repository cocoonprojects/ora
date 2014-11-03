<?php
namespace Ora\Accounting;

/**
 * 
 * @author andreabandera
 *
 */
class CreditsAccountCreatedEvent extends CreditsAccountEvent {
	
	public function getCreatedAt() {
		$d = $this->toPayloadReader()->stringValue('createdAt');
		return date_create_from_format('Y-m-d H:i:s', $d);
	}

}
<?php
namespace  Ora\Accounting;

class CreditsWithdrawnEvent extends CreditsAccountEvent {
	
	public function getValue() {
		return $this->toPayloadReader()->floatValue('value');
	}
	
}
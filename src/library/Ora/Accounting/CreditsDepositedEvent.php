<?php
namespace  Ora\Accounting;

class CreditsDepositedEvent extends CreditsAccountEvent {
	
	public function getValue() {
		return $this->toPayloadReader()->floatValue('value');
	}
	
}
<?php
namespace  Ora\Accounting;

class CreditsDepositedEvent extends CreditsAccountEvent {
	
	protected $value;
	
	public function __construct(\DateTime $firedAt, CreditsAccount $account, $value) {
		parent::__construct($firedAt, $account);
		assertGreaterThanOrEqual(0, $value, 'Invalid deposit lower than 0');
		$this->value = $value;
	}
	
	public function getValue() {
		return $value;
	}
}
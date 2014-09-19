<?php
namespace  Ora\Accounting;

class CreditsDepositedEvent extends CreditsAccountEvent {
	
	protected $value;
	
	public function __construct(\DateTime $firedAt, CreditsAccount $account, $value) {
		parent::__construct($firedAt, $account);
		assertNotNull($account, 'Unable to deposit '.$value.' credits in null account');
		$this->value = $value;
	}
	
	public function getValue() {
		return $value;
	}
}
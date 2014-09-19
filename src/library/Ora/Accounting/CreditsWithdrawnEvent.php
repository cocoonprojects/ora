<?php
namespace  Ora\Accounting;

final class CreditsWithdrawnEvent extends CreditsAccountEvent {
	
	private $value;
	
	public function __construct(\DateTime $firedAt, CreditsAccount $account, $value) {
		parent::__construct($firedAt, $account);
		$this->value = $value;
	}
	
	public function getValue() {
		return $value;
	}
}
<?php
namespace Ora\CreditsAccount;

use Ora\Entity;

class CreditsAccount extends Entity {
	
	private $balance;
	
	public function setBalance(Balance $balance) {
		$this->balance = $balance;
	}
	
	public function getBalance() {
		return $this->balance;
	}
}
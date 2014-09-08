<?php
namespace Ora\CreditsAccount;

class EventSourcingCreditsAccountsService implements CreditsAccountsService {
	
	public function create() {
		$rv = new CreditsAccount('123456', new \DateTime());
		$rv->setBalance(new Balance(1500, new \DateTime()));
		return $rv;
	}
	
	public function listAccounts() {
		$rv = new CreditsAccount('123458', new \DateTime());
		$rv->setBalance(new Balance(1500, new \DateTime()));
		return array($rv, $rv);
	}
	
	public function getAccount($id) {
		$rv = new CreditsAccount($id, new \DateTime());
		$rv->setBalance(new Balance(1500, new \DateTime()));
		return $rv;
	}

}
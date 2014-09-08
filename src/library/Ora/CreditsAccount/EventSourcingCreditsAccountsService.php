<?php
namespace Ora\CreditsAccount;

class EventSourcingCreditsAccountsService implements CreditsAccountsService {
	
	public function create() {
		$rv = new CreditsAccount('123456', new \DateTime());
		$rv->setBalance(new Balance(1500, new \DateTime()));
		return $rv;
	}

}
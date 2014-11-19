<?php
namespace Ora\Accounting;

interface CreditsAccountsService {
	
	/**
	 * Currency should be based on the organization
	 * @param string $currency
	 */
	public function create();
	
	public function listAccounts();
	
	public function getAccount($id);
	
	public function deposit(CreditsAccount $destination, $value);
	
	public function withdraw(CreditsAccount $source, $value); 
	
	public function transfer(CreditsAccount $source, CreditsAccount $destination, $value, \DateTime $when);

}
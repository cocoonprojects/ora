<?php
namespace Ora\Accounting;

interface CreditsAccountsService {
	
	/**
	 * Currency should be based on the organization
	 * @param string $currency
	 */
	public function create($currency = null);
	
	public function listAccounts();
	
	public function getAccount($id);
	
	public function transfer(CreditsAccount $source, CreditsAccount $destination, $value, \DateTime $when);

}
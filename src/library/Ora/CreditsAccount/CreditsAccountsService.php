<?php
namespace Ora\CreditsAccount;

interface CreditsAccountsService {
	
	public function create();
	
	public function listAccounts();
	
	public function getAccount($id);

}
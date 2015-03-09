<?php
namespace Ora\Accounting;

use Ora\User\User;
use Ora\Organization\Organization;

interface AccountService {
	
	public function createPersonalAccount(User $holder);
	
	public function createOrganizationAccount(User $holder, Organization $organization);	
	
	public function getAccount($id);
	
	public function transfer(Account $source, Account $destination, $value, \DateTime $when);
	
	public function findAccounts(User $user);
	
	public function findAccount($id);

}
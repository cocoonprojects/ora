<?php

namespace Accounting\Service;

use Ora\User\User;
use Application\Organization;

interface AccountService {
	
	public function createPersonalAccount(User $holder);
	
	public function createOrganizationAccount(Organization $organization, User $holder);	
	
	public function getAccount($id);
	
	public function findAccounts(User $user);
	
	public function findAccount($id);
	
	public function findPersonalAccount(User $user);

}
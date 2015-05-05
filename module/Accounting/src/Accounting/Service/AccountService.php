<?php

namespace Accounting\Service;

use Application\Entity\User;
use People\Organization;
use Accounting\Entity\OrganizationAccount;
use Accounting\Entity\Account;

interface AccountService
{
	public function createPersonalAccount(User $holder);
	
	public function createOrganizationAccount(Organization $organization, User $holder);	
	
	public function getAccount($id);
	
	public function findAccounts(User $user);
	
	public function findAccount($id);
	/**
	 * 
	 * @param User $user
	 * @return Account
	 */
	public function findPersonalAccount(User $user);
	/**
	 * 
	 * @param string|Uuid $organizationId
	 * @return OrganizationAccount
	 */
	public function findOrganizationAccount($organizationId);
}
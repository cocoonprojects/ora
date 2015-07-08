<?php

namespace Accounting\Service;

use Accounting\Entity\OrganizationAccount;
use Accounting\Entity\Account;
use Application\Entity\User;
use People\Organization;

interface AccountService
{
	public function createPersonalAccount(User $holder, Organization $organization);
	
	public function createOrganizationAccount(Organization $organization, User $holder);
	
	public function getAccount($id);
	
	public function findAccounts(User $user);
	
	public function findAccount($id);
	/**
	 * 
	 * @param string|User $user
	 * @param string|Organization $organization
	 * @return Account
	 */
	public function findPersonalAccount($user, $organization);
	/**
	 * 
	 * @param string|Organization $organization
	 * @return OrganizationAccount
	 */
	public function findOrganizationAccount($organization);
}
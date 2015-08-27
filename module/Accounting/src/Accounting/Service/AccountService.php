<?php

namespace Accounting\Service;

use Accounting\Account;
use Accounting\Entity\OrganizationAccount;
use Accounting\Entity\PersonalAccount;
use Application\Entity\User;
use People\Entity\Organization as ReadModelOrganization;
use People\Organization;

interface AccountService
{
	public function createPersonalAccount(User $holder, Organization $organization);
	
	public function createOrganizationAccount(Organization $organization, User $holder);

	/**
	 * @param $id
	 * @return null|Account
	 */
	public function getAccount($id);

	/**
	 * @param User $user
	 * @param ReadModelOrganization $organization
	 * @return Account[]
	 */
	public function findAccounts(User $user, ReadModelOrganization $organization);

	public function findAccount($id);
	/**
	 * 
	 * @param string|User $user
	 * @param string|Organization $organization
	 * @return PersonalAccount
	 */
	public function findPersonalAccount($user, $organization);
	/**
	 * 
	 * @param string|Organization $organization
	 * @return OrganizationAccount
	 */
	public function findOrganizationAccount($organization);
}
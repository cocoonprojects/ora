<?php

namespace Accounting\Service;

use Accounting\Account;
use Accounting\Entity\OrganizationAccount;
use Accounting\Entity\PersonalAccount;
use Accounting\Entity\Account as ReadModelAccount;
use Application\Entity\User;
use People\Entity\Organization as ReadModelOrganization;
use People\Organization;
use Accounting\Entity\Transaction;

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

	/**
	 * @param ReadModelAccount $account
	 * @param integer $limit
	 * @param integer $offset
	 * @param array $filters
	 * @return \Accounting\Entity\Transaction[]
	 */
	public function findTransactions(ReadModelAccount $account, $limit, $offset, array $filters = []);

	/**
	 * @param ReadModelAccount $account
	 * @param array $filters
	 * @return int
	 */
	public function countTransactions(ReadModelAccount $account, array $filters = []);
	/**
	 *
	 * @param ReadModelAccount $payer
	 * @param ReadModelAccount $payee
	 * @param string $amount
	 * @param string $description
	 * @param User $by
	 */
	public function transfer(Account $payer, Account $payee, $amount, $description, User $by);
}
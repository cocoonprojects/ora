<?php
namespace Accounting;

use Rhumsaa\Uuid\Uuid;
use People\Organization;
use Application\Entity\User;

class OrganizationAccount extends Account
{
	public static function create(Organization $organization, User $createdBy) {
		$rv = parent::create($organization, $createdBy);
		$organization->changeAccount($rv, $createdBy);
		return $rv;
	}

	/**
	 * Returns the string identifier of the Resource
	 *
	 * @return string
	 */
	public function getResourceId()
	{
		return 'Ora\OrganizationAccount';
	}
}
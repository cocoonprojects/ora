<?php
namespace Accounting;

use Rhumsaa\Uuid\Uuid;
use People\Organization;
use Application\Entity\User;

class PersonalAccount extends Account
{
	public static function create(Organization $organization, User $createdBy) {
		$rv = parent::create($organization, $createdBy);
		return $rv;
	}
}
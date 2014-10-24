<?php

namespace Ora\Organization;

use Ora\Organization\Organization;
use Ora\User\User;

/**
 * @author Giannotti Fabio
 */
interface OrganizationService
{
	public function addUser(Organization $organization, User $user);
	
	public function findOrganization($id);
}
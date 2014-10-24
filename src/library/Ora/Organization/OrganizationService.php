<?php

namespace Ora\Organization;

use Ora\Organization\Organization;
use Ora\User\User;

interface OrganizationService
{

	public function addUser(Organization $organization, User $user);
	
	public function findOrganization($id);
}
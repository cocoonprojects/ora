<?php

namespace Ora\Organization;

use Ora\User\User;

interface OrganizationService
{	
	public function findOrganization($id);
	
	public function findOrganizationUsers(User $user);	
}
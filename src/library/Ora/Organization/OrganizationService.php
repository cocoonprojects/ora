<?php

namespace Ora\Organization;

use Ora\User\User;

interface OrganizationService
{	
	public function findOrganization($id);
	
	//public function findUserOrganizationMembership(User $user);

	//public function findOrganizationMembership();
}
<?php

namespace Ora\Organization;

use Ora\User\User;
use Ora\ReadModel\Organization as OrganizationReadModel;

interface OrganizationService
{	
	public function findOrganization($id);
	
	public function findUserOrganizationMembership(User $user);

	public function findOrganizationMembership(OrganizationReadModel $organization);
	
}
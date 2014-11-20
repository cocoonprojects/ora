<?php

namespace Ora\ProjectManagement;

use Ora\ReadModel\Organization;

/**
 * @author Giannotti Fabio
 */
interface ProjectService
{
	public function getProject($id);
	
	public function findOrganizationProjects(Organization $organization);
	
} 
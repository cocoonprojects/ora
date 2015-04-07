<?php

namespace Application\Service;

use Ora\User\User;
use Application\Organization;
use Ora\ReadModel\Organization as OrganizationReadModel;
use Ora\ReadModel\OrganizationMembership;

interface OrganizationService
{	
	/**
	 * 
	 * @param string $name
	 * @param User $createdBy
	 * @return Organization
	 */
	public function createOrganization($name, User $createdBy);
	/**
	 * 
	 * @param string $id
	 * @return Organization
	 */
	public function getOrganization($id);
	/**
	 * 
	 * @param string $id
	 * @return OrganizationReadModel
	 */
	public function findOrganization($id);
	/**
	 * 
	 * @param User $user
	 * @return OrganizationMembership[]
	 */
	public function findUserOrganizationMemberships(User $user);
	/**
	 * 
	 * @param OrganizationReadModel $organization
	 * @return OrganizationMembership[]
	 */
	public function findOrganizationMemberships(OrganizationReadModel $organization);
	
}
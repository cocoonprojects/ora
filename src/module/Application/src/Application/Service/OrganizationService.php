<?php

namespace Application\Service;

use Application\Entity\User;
use Application\Organization;
use Application\Entity\Organization as OrganizationReadModel;
use Application\Entity\OrganizationMembership;

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
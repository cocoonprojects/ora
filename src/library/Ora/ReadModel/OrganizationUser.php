<?php

namespace Ora\ReadModel;

use Doctrine\ORM\Mapping AS ORM;
use Ora\ReadModel\Organization;
use Ora\User\User;

/**
 * @ORM\Entity @ORM\Table(name="organization_users")
 *
 */
class OrganizationUser extends DomainEntity 
{	
	const ROLE_ADMIN = "Admin";
	const ROLE_MEMBER = "Member";
		
	/**
	 * @ORM\Column(type="string")
	 * @var string
	 */
	private $organizationRole;
			
	/**
	 * @ORM\ManyToOne(targetEntity="Ora\User\User")
	 * @ORM\Id
	 */
	protected $user;

	/**
	 * @ORM\ManyToOne(targetEntity="Ora\ReadModel\Organization")
	 * @ORM\Id
	 */
	protected $organization;
		
	public function __construct(User $user, Organization $organization)
	{
		$this->user = $user;
		$this->organization = $organization;
	}		

	public function setOrganizationRole($role)
	{
		$this->organizationRole = $role;
	}
		
	public function setUser(User $user)
	{
		$this->user = $user;
	}
	
	public function setOrganization(Organization $organization)
	{
		$this->organization = $organization;
	}
		
	public function getOrganizationRole()
	{
		return $this->organizationRole;
	}
			
	public function getUser()
	{
		return $this->user;
	}	
	
	public function getOrganization()
	{
		return $this->organization;
	}
	
}
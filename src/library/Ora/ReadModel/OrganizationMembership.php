<?php

namespace Ora\ReadModel;

use Doctrine\ORM\Mapping AS ORM;
use Ora\User\User;

/**
 * @ORM\Entity @ORM\Table(name="organization_members")
 *
 */
class OrganizationMembership extends EditableEntity 
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
	protected $member;

	/**
	 * @ORM\ManyToOne(targetEntity="Ora\ReadModel\Organization")
	 * @ORM\Id
	 */
	protected $organization;
		
	public function __construct(User $member, Organization $organization)
	{
		$this->member = $member;
		$this->organization = $organization;
	}		

	public function setOrganizationRole($role)
	{
		$this->organizationRole = $role;
	}
		
	public function setMember(User $member)
	{
		$this->member = $member;
	}
	
	public function setOrganization(Organization $organization)
	{
		$this->organization = $organization;
	}
		
	public function getOrganizationRole()
	{
		return $this->organizationRole;
	}
			
	public function getMember()
	{
		return $this->member;
	}	
	
	public function getOrganization()
	{
		return $this->organization;
	}
	
}
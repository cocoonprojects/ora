<?php

namespace Ora\UserOrganization;

use Doctrine\ORM\Mapping AS ORM;
use Ora\DomainEntity;

/**
 * @ORM\Entity @ORM\Table(name="organization_users")
 *
 */
class UserOrganization  
{	
	const ROLE_ADMIN = "Admin";
	const ROLE_MEMBER = "Member";
		
	/**
	 * @ORM\Column(type="string")
	 * @var string
	 */
	private $organizationRole;
	
	/**
	 * @ORM\Column(type="datetime")
	 * @var DateTime
	 */
	private $createdAt;
	
	/**
	 * @ORM\ManyToOne(targetEntity="Ora\User\User")
	 */
	protected $createdBy;
		
	/**
	 * @ORM\ManyToOne(targetEntity="Ora\User\User", inversedBy="userOrganizations")
	 * @ORM\Id
	 */
	protected $user;

	/**
	 * @ORM\ManyToOne(targetEntity="Ora\Organization\Organization")
	 * @ORM\Id
	 */
	protected $organization;
	

	public static $organizationRoleMap = array(
			ROLE_ADMIN           => self::ROLE_ADMIN,
			ROLE_MEMBER          => self::ROLE_MEMBER,
	);
	
	public function __construct(\DateTime $createdAt, $createdBy, $user, $organization, $organizationRole)
	{
		$this->organizationRole = $organizationRole;
		$this->createdAt = $createdAt;
		$this->createdBy = $createdBy;			
		$this->user = $user;
		$this->organization = $organization;
	}	
	
	public function getCreatedAt()
	{
		return $this->createdAt;
	}
	
	public function getCreatedBy()
	{
		return $this->createdBy;
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
	
	public function serializeToJSON($entitySerializer)
	{
		$serializedToArray = $this->serializeToARRAY($entitySerializer);
		 
		return json_encode($serializedToArray);
	}

	public function serializeToARRAY($entitySerializer)
	{
		$serializedToArray = $entitySerializer->toArray($this);
	
		return $serializedToArray;
	}	
}
<?php

namespace Ora\ReadModel;

use Doctrine\ORM\Mapping AS ORM;
use Ora\User\User;

/**
 * @ORM\Entity @ORM\Table(name="organization_members")
 *
 */
class OrganizationMembership 
{	
	const ROLE_ADMIN = "admin";
	const ROLE_MEMBER = "member";
		
	/**
	 * @ORM\Column(type="string")
	 * @var string
	 */
	private $role;
			
	/**
	 * @ORM\Id
	 * @ORM\ManyToOne(targetEntity="Ora\User\User", inversedBy="memberships")
     * @ORM\JoinColumn(name="member_id", referencedColumnName="id", onDelete="CASCADE")
	 */
	protected $member;

	/**
	 * @ORM\Id
	 * @ORM\ManyToOne(targetEntity="Ora\ReadModel\Organization")
     * @ORM\JoinColumn(name="organization_id", referencedColumnName="id", onDelete="CASCADE")
	 */
	protected $organization;
		
	/**
	 * @ORM\Column(type="datetime")
	 * @var DateTime
	 */
	protected $createdAt;
	
	/**
	 * @ORM\ManyToOne(targetEntity="Ora\User\User")
     * @ORM\JoinColumn(name="createdBy_id", referencedColumnName="id")
	 */
	protected $createdBy;
	
    /**
     * @ORM\Column(type="datetime")
     * @var datetime
     */
    protected $mostRecentEditAt;
    
    /**
     * @ORM\ManyToOne(targetEntity="Ora\User\User")
     * @ORM\JoinColumn(name="mostRecentEditBy_id", referencedColumnName="id")
     */
    protected $mostRecentEditBy;
	
	public function __construct(User $user, Organization $organization)
	{
		$this->member = $user;
		$this->organization = $organization;
	}		

	public function setRole($role)
	{
		$this->role = $role;
		return $this;
	}
		
	public function getRole()
	{
		return $this->role;
	}
			
	public function getMember()
	{
		return $this->member;
	}	
	
	public function getOrganization()
	{
		return $this->organization;
	}
	
	public function getCreatedAt() {
		return $this->createdAt;
	}
	
	public function setCreatedAt(\DateTime $when) {
		$this->createdAt = $when;
		return $this;
	}
	
    public function getCreatedBy() {
        return $this->createdBy;
    }
    
    public function setCreatedBy(User $user) {
    	$this->createdBy = $user;
    	return $this;
    }

    public function getMostRecentEditAt() {
        return $this->mostRecentEditAt;
    }
    
	public function setMostRecentEditAt(\DateTime $when) {
		$this->mostRecentEditAt = $when;
		return $this;
	}
	
    public function getMostRecentEditBy() {
        return $this->mostRecentEditBy;
    }
    
    public function setMostRecentEditBy(User $user) {
    	$this->mostRecentEditBy = $user;
    	return $this;
    }

}
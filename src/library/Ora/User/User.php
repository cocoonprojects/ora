<?php

namespace Ora\User;

use Doctrine\ORM\Mapping AS ORM;
use Ora\DomainEntity;

use Ora\User\Role;
use Doctrine\Common\Collections\ArrayCollection;
use Ora\Organization\Organization;
use Ora\UserOrganization\UserOrganization;
use Rhumsaa\Uuid\Uuid;

/**
 * @ORM\Entity @ORM\Table(name="users")
 *
 */
class User extends DomainEntity implements \Serializable, MasterData
{	   
	const STATUS_ACTIVE = 1;
	 
	/**
	 * @ORM\Column(type="string", length=100, nullable=TRUE)
	 * @var string
	 */
	private $firstname;

	/**
	 * @ORM\Column(type="string", length=100, nullable=TRUE)
	 * @var string
	 */
	private $lastname;

	/**
	 * @ORM\Column(type="string", length=200, unique=TRUE)
	 * @var string
	 */
	private $email;
		
	/**
	 * @ORM\Column(type="integer", options={"default" = "0"})
	 * @var boolean
	 */
	private $status;
	
	/**
	 * @ORM\Embedded(class="Role")
	 * @var Role
	 */
	private $systemRole;	
	
	/**
	 * @ORM\OneToMany(targetEntity="Ora\UserOrganization\UserOrganization", mappedBy="user")
	 **/
	private $userOrganizations;	
	
	public function __construct(Uuid $userID, MasterData $createdBy = null, \DateTime $createdAt = null) 
	{
		$this->id = $userID;
		$this->createdAt = $createdAt == null ? new \DateTime() : $createdAt;
		if(!is_null($createdBy)) {
			$this->createdBy = $createdBy->toProfile();
		}
		
		$this->userOrganizations = new ArrayCollection();
		$this->setStatus(self::STATUS_ACTIVE);
	}
	
	public function setFirstname($firstname)
	{
		$this->firstname = $firstname;
	}
	
	public function getFirstname()
	{
		return $this->firstname;
	}

	public function setLastname($lastname)
	{
		$this->lastname = $lastname;
	}
	
	public function getLastname()
	{
		return $this->lastname;
	}

	public function setEmail($email)
	{
		$this->email = $email;
	}
	
	public function getEmail()
	{
		return $this->email;
	}

	public function setStatus($status)
	{
		$this->status = $status;
	}
	
	public function getStatus()
	{
		return $this->status;
	}	
	
	public function setSystemRole(Role $role)
	{
		$this->systemRole = $role;
	}
	
	public function getSystemRole()
	{
		$this->systemRole->getName();
	}
	
	public function getUserOrganizations()
	{
		return $this->userOrganizations;
	}
	
	public function toProfile() {
		$rv = Profile::create($this->id, $this->createdAt, $this->createdBy);
		$rv->setFirstname($this->firstname);
		$rv->setLastname($this->lastname);
		$rv->setEmail($this->email);
		return $rv;
	}
	
	public function serialize()
	{
		$data = array(
			'id' => $this->id->toString(),
			'email' => $this->email,
			'firstname' => $this->firstname,
			'lastname' => $this->lastname,
		);
	    return serialize($data); 
	}
	
	public function unserialize($encodedData)
	{
	    $data = unserialize($encodedData);
	    $this->id = Uuid::fromString($data['id']);
	    $this->email = $data['email'];
	    $this->firstname = $data['firstname'];
	    $this->lastname = $data['lastname'];
	}
}
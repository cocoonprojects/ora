<?php

namespace Ora\User;

use Doctrine\ORM\Mapping AS ORM;
use Ora\DomainEntity;
use Ora\User\Role as Role;

/**
 * @ORM\Entity @ORM\Table(name="users")
 *
 */
class User extends DomainEntity 
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
			
	// TODO: Utilizzare Ora\User\User $createdBy se createdBy dev'essere una relazione con lo USER
	public function __construct($userID, \DateTime $createdAt, $createdBy) 
	{
		parent::__construct($userID, $createdAt, $createdBy);		
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
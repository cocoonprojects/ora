<?php

namespace Ora\User;

use Doctrine\ORM\Mapping AS ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Rhumsaa\Uuid\Uuid;
use Ora\ReadModel\OrganizationMembership;
use Ora\ReadModel\EditableEntity;

/**
 * @ORM\Entity @ORM\Table(name="users")
 *
 */
class User extends EditableEntity
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
	 * @ORM\Column(type="integer")
	 * @var int
	 */
	private $status;
	
	/**
	 * @ORM\Column(type="string")
	 * @var string
	 */
	private $picture;
	
	/**
	 * @ORM\OneToMany(targetEntity="Ora\ReadModel\OrganizationMembership", mappedBy="member", fetch="LAZY")
	 * @var ArrayCollection
	 */
	private $memberships;
	
	public function __construct($id) {
		$this->id = $id;
		$this->memberships = new ArrayCollection();
		var_dump('Passo nel costruttore di User con');
		var_dump($this->memberships);
	}
	
	public static function create(User $createdBy = null) {
		$rv = new self(Uuid::uuid4()->toString());
		$rv->status = self::STATUS_ACTIVE;
		$rv->createdAt = new \DateTime();
		$rv->createdBy = $createdBy;
		$rv->mostRecentEditAt = $rv->createdAt;
		$rv->mostRecentEditBy = $rv->createdBy;
		return $rv;
	}
	
	public function setFirstname($firstname)
	{
		$this->firstname = $firstname;
		return $this;
	}
	
	public function getFirstname()
	{
		return $this->firstname;
	}

	public function setLastname($lastname)
	{
		$this->lastname = $lastname;
		return $this;
	}
	
	public function getLastname()
	{
		return $this->lastname;
	}

	public function setEmail($email)
	{
		$this->email = $email;
		return $this;
	}
	
	public function getEmail()
	{
		return $this->email;
	}

	public function setStatus($status)
	{
		$this->status = $status;
		return $this;
	}
	
	public function getStatus()
	{
		return $this->status;
	}	
	
	public function getOrganizationMemberships()
	{
		return $this->memberships;
	}
	
	public function setPicture($url) {
		$this->picture = $url;
		return $this;
	}
	
	public function getPicture() {
		return $this->picture;
	}
}
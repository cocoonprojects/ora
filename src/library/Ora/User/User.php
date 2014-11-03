<?php

namespace Ora\User;

use Doctrine\ORM\Mapping AS ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Rhumsaa\Uuid\Uuid;
use Ora\DomainEntity;

/**
 * @ORM\Entity @ORM\Table(name="users")
 *
 */
class User extends DomainEntity implements \Serializable
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
	
	public static function create(Uuid $userID, User $createdBy = null) {
		$rv = new self();
		$rv->id = $userID;
		$rv->status == self::STATUS_ACTIVE;
		/**
		 * TODO: implementare l'Event Sourcing
		 */
		return $rv;
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
	
	public function serialize()
	{
		$data = array(
			'id' => $this->id->toString(),
			'email' => $this->email,
			'firstname' => $this->firstname,
			'lastname' => $this->lastname,
			'status' => $this->status,
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
	    $this->status = $data['status'];
	}
}
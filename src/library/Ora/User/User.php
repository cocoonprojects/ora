<?php

namespace Ora\User;

use Doctrine\ORM\Mapping AS ORM;
use Ora\DomainEntity;

/**
 * @ORM\Entity @ORM\Table(name="users")
 * @author Giannotti Fabio
 *
 */
class User extends DomainEntity 
{	    
	/**
	 * @ORM\Column(type="string")
	 * @var string
	 */
	private $name;
	
	// TODO: Utilizzare Ora\User\User $createdBy se createdBy dev'essere una relazione con lo USER
	public function __construct($userID, \DateTime $createdAt, $createdBy) 
	{
		parent::__construct($userID, $createdAt, $createdBy);
	}
	
	public function getName() {
		return $this->name;
	}
	
	public function setUser($name) {
		$this->name = $name;
	}
}
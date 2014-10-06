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
	
	public function __construct($userID, \DateTime $createdAt) 
	{
		parent::__construct($userID, $createdAt);
	}
	
	public function getName() {
		return $this->name;
	}
	
	public function setUser($name) {
		$this->name = $name;
	}
}
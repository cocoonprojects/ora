<?php

namespace Ora\Organization;

use Doctrine\ORM\Mapping AS ORM;
use Ora\DomainEntity;

/**
 * @ORM\Entity @ORM\Table(name="organizations")
 * @author Giannotti Fabio
 */
class Organization extends DomainEntity 
{	    
	/**
	 * @ORM\Column(type="string")
	 * @var string
	 */
	private $subject;
	
	// TODO: Utilizzare Ora\User\User $createdBy se createdBy dev'essere una relazione con lo USER
	public function __construct($organizationID, \DateTime $createdAt, $createdBy) 
	{
		parent::__construct($organizationID, $createdAt, $createdBy);
	}
	
	public function getSubject() {
		return $this->subject;
	}
	
	public function setSubject($subject) {
		$this->subject = $subject;
	}
	
} 
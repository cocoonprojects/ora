<?php

namespace Ora\ReadModel;

use Doctrine\ORM\Mapping AS ORM;
use Ora\ReadModel\DomainEntity;

/**
 * @ORM\Entity @ORM\Table(name="organizations")
 */
class Organization extends DomainEntity
{	    
	/**
	 * @ORM\Column(type="string")
	 * @var string
	 */
	private $subject;
	
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
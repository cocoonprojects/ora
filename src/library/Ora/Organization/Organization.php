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
	
	public function __construct(\DateTime $createdAt) 
	{
		parent::__construct($createdAt);
	}
	
	public function getSubject() {
		return $this->subject;
	}
	
	public function setSubject($subject) {
		$this->subject = $subject;
	}
	
} 
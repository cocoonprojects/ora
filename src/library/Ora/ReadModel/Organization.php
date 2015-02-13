<?php

namespace Ora\ReadModel;

use Doctrine\ORM\Mapping AS ORM;
use Ora\ReadModel\DomainEntity;

/**
 * @ORM\Entity @ORM\Table(name="organizations")
 */
class Organization extends EditableEntity
{	    
	/**
	 * @ORM\Column(type="string")
	 * @var string
	 */
	private $name;
	
	public function __construct($organizationID, \DateTime $createdAt, $createdBy) 
	{
		parent::__construct($organizationID);
		parent::setCreatedAt($createdAt);
		parent::setCreatedBy($createdBy);
	}
	
	public function getName() {
		return $this->name;
	}
	
	public function setName($name) {
		$this->name = $name;
	}		
} 
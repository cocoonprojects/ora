<?php

namespace Ora\ProjectManagement;

use Doctrine\ORM\Mapping AS ORM;
use Ora\DomainEntity;

/**
 * @ORM\Entity @ORM\Table(name="projects")
 * @author Giannotti Fabio
 *
 */
class Project extends DomainEntity 
{	    
	/**
	 * @ORM\Column(type="string")
	 * @var string
	 */
	private $subject;
	
	public function __construct($projectID, \DateTime $createdAt) 
	{
		parent::__construct($projectID, $createdAt);
	}
	
	public function getSubject() {
		return $this->subject;
	}
	
	public function setSubject($subject) {
		$this->subject = $subject;
	}
}
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
	
	// TODO: Utilizzare Ora\User\User $createdBy se createdBy dev'essere una relazione con lo USER
	public function __construct($projectID, \DateTime $createdAt, Ora\User\User $createdBy) 
	{
		parent::__construct($projectID, $createdAt, $createdBy);
	}
	
	public function getSubject() {
		return $this->subject;
	}
	
	public function setSubject($subject) {
		$this->subject = $subject;
	}
}
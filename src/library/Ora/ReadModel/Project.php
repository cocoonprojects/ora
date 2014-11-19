<?php

namespace Ora\ReadModel;

use Doctrine\ORM\Mapping AS ORM;
use Ora\ReadModel\DomainEntity;

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
	
	public function getSubject() {
		return $this->subject;
	}
	
	public function setSubject($subject) {
		$this->subject = $subject;
	}
}
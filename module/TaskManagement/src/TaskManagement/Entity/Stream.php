<?php

namespace TaskManagement\Entity;

use Doctrine\ORM\Mapping AS ORM;
use Application\Entity\EditableEntity;
use Application\Entity\Organization;

/**
 * @ORM\Entity @ORM\Table(name="streams")
 * @author Giannotti Fabio
 *
 */
class Stream extends EditableEntity
{	    
	/**
	 * @ORM\Column(type="string", nullable=true)
	 * @var string
	 */
	private $subject;
	
	/**
	 * @ORM\ManyToOne(targetEntity="Application\Entity\Organization")
	 * @ORM\JoinColumn(name="organization_id", referencedColumnName="id", nullable=false)
	 * @var Organization
	 */
	private $organization;	
	
	public function getSubject() {
		return $this->subject;
	}
	
	public function setSubject($subject) {
		$this->subject = $subject;
	}
	
	public function getOrganization() {
		return $this->organization;
	}
	
	public function setOrganization(Organization $organization) {
		$this->organization = $organization;
		return $this;
	}	
}
<?php

namespace Ora\ReadModel;

use Doctrine\ORM\Mapping AS ORM;
use Zend\Permissions\Acl\Resource\ResourceInterface;
use Ora\StreamManagement\ReadableStream;

/**
 * @ORM\Entity @ORM\Table(name="streams")
 * @author Giannotti Fabio
 *
 */
class Stream extends EditableEntity implements ResourceInterface, ReadableStream
{	    
	/**
	 * @ORM\Column(type="string")
	 * @var string
	 */
	private $subject;
	
	/**
	 * @ORM\ManyToOne(targetEntity="Ora\ReadModel\Organization")
	 * @ORM\JoinColumn(name="organization_id", referencedColumnName="id", nullable=false)
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
		return $this->organization;
	}	
	
 	public function getReadableOrganization(){
    	return $this->organization->getId();
    }

	public function getResourceId(){			
        return get_class($this);
    }
}
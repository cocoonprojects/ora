<?php

namespace Ora\StreamManagement;

use Ora\DomainEntity;
use Ora\User\User;
use Rhumsaa\Uuid\Uuid;
use Ora\ReadModel\Organization;
use Zend\Permissions\Acl\Resource\ResourceInterface;

/**
 * 
 * @author Giannotti Fabio
 *
 */
class Stream extends DomainEntity implements \Serializable, ResourceInterface
{	    
	/**
	 * 
	 * @var string
	 */
	private $subject;
	
	/**
	 *
	 * @var Uuid
	 */
	private $organizationId;	
	
	public function __construct(Uuid $id, User $createdBy, Organization $organization, \DateTime $createdAt = null) 
	{
		$this->id = $id;
		$this->createdAt = $createdAt == null ? new \DateTime() : $createdAt;
		$this->createdBy = $createdBy;
		$this->changeOrganization($organization, $createdBy);
	}
	
	public function getSubject() {
		return $this->subject;
	}
	
	public function setSubject($subject) {
		$this->subject = $subject;
	}

	public function serialize()
	{
		$data = array(
			'id' => $this->id->toString(),
			'subject' => $this->subject,
		);
	    return serialize($data); 
	}
	
	public function unserialize($encodedData)
	{
	    $data = unserialize($encodedData);
	    $this->id = Uuid::fromString($data['id']);
	    $this->subject = $data['subject'];
	}
	
	public function changeOrganization(Organization $organization, User $updatedBy){
		
		$payload = array(
				'organizationId' => $organization->getId(),
				'by' => $updatedBy->getId(),
		);
		if(!is_null($this->organizationId)) {
			$payload['prevOrganizationId'] = $this->organizationId->toString();
		}
		
		$this->recordThat(OrganizationChanged::occur($this->id->toString(), $payload));
	}
	
	public function whenOrganizationChanged(OrganizationChanged $event){
		
		$p = $event->payload();
		$this->organizationId = Uuid::fromString($p['organizationId']);
	}
	
	public function getOrganizationId(){
    	return $this->organizationId;
    }

	public function getResourceId(){			
        return get_class($this);
    }
	
}
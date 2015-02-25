<?php

namespace Ora\StreamManagement;

use Ora\DomainEntity;
use Ora\User\User;
use Rhumsaa\Uuid\Uuid;

/**
 * 
 * @author Giannotti Fabio
 *
 */
class Stream extends DomainEntity implements \Serializable
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
	
	public function __construct(Uuid $id, User $createdBy, \DateTime $createdAt = null) 
	{
		$this->id = $id;
		$this->createdAt = $createdAt == null ? new \DateTime() : $createdAt;
		$this->createdBy = $createdBy;
	}
	
	public function getSubject() {
		return $this->subject;
	}
	
	public function setSubject($subject) {
		$this->subject = $subject;
	}

	public function setOrganization($organization) {
		$this->organization = $organization;
	}
	
	public function getOrganization() {
		return $this->organization;
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
	
}
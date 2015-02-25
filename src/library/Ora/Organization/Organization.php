<?php

namespace Ora\Organization;

use Ora\DomainEntity;
use Rhumsaa\Uuid\Uuid;
use Ora\User\User;


class Organization extends DomainEntity implements \Serializable
{	    
	/**
	 * 
	 * @var string
	 */
	private $name;
		
	
	public function __construct(Uuid $id, User $createdBy, \DateTime $createdAt = null) 
	{
		$this->id = $id;
		$this->createdAt = $createdAt == null ? new \DateTime() : $createdAt;
		$this->createdBy = $createdBy;
	}
	
	public function getName() {
		return $this->name;
	}
	
	public function setName($name) {
		$this->name = $name;
	}

	public function serialize()
	{
		$data = array(
			'id' => $this->id->toString(),
			'name' => $this->name,
		);
	    return serialize($data); 
	}
	
	public function unserialize($encodedData)
	{
	    $data = unserialize($encodedData);
	    $this->id = Uuid::fromString($data['id']);
	    $this->name = $data['name'];
	}
	
}
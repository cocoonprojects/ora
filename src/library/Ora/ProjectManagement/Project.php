<?php

namespace Ora\ProjectManagement;

use Doctrine\ORM\Mapping AS ORM;
use Ora\DomainEntity;
use Rhumsaa\Uuid\Uuid;
use Ora\User\User;

/**
 * @ORM\Entity @ORM\Table(name="projects")
 * @author Giannotti Fabio
 *
 */
class Project extends DomainEntity implements \Serializable
{	    
	/**
	 * @ORM\Column(type="string")
	 * @var string
	 */
	private $subject;
	
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
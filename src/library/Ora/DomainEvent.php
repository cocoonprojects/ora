<?php

namespace Ora;

use Doctrine\ORM\Mapping AS ORM;
use \DateTime;

/**
 * @ORM\Entity
 * @ORM\Table(name="domainEvents")
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="event_type", type="string")
 */
class DomainEvent 
{	
    /**
     * @ORM\Id @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     */
    private $id;
    
    /**
     * @ORM\Column(type="datetime")
     */
    private $firedAt;
    
	/**
	 * @ORM\Column(type="string", nullable=TRUE)
	 */
	protected $aggregateId;
	
	/**
	 * @ORM\Column(type="json_array")
	 */
	protected $attributes;
	
	protected function __construct(DateTime $firedAt) 
	{
<<<<<<< HEAD
	    $this->firedAt = $firedAt;
=======
	    $this->setFiredAt($firedAt);
>>>>>>> develop
	}
	
	public function getId() {
		return $this->id;
	}
	
	public function getFiredAt() {
	    return $this->firedAt;
	}
	
<<<<<<< HEAD
	public function getAggregateId() {
		return $this->aggregateId;
	}
=======
	public function setFiredAt($firedAt) {
	    $this->firedAt = $firedAt;
	}
	
	public function getAggregateId() {
		return $this->aggregateId;
	}
	
	public function setAggregateId($aggregateId) {
	    $this->aggregateId = $aggregateId;
	}
>>>>>>> develop
		
	public function getAttributes() {
	    return $this->attributes;
	}
<<<<<<< HEAD
=======
	
	public function setAttributes($attributes) {
	    $this->attributes = $attributes;
	}
	
>>>>>>> develop
}
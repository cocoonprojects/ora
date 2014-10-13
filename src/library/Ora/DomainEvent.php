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
     * @var integer
     */
    private $id;
    
    /**
     * @ORM\Column(type="datetime")
     * @var datetime
     */
    private $firedAt;
    
	/**
	 * @ORM\Column(type="string", nullable=TRUE)
	 * @var string
	 */
	protected $aggregateId;
	
	/**
	 * @ORM\Column(type="json_array")
	 * @var json_array
	 */
	protected $attributes;
	
	protected function __construct(DateTime $firedAt)
	{
	    $this->firedAt = $firedAt;
	}
	
	public function getId() {
		return $this->id;
	}
	
	public function getFiredAt() {
	    return $this->firedAt;
	}
	
	public function getAggregateId() {
		return $this->aggregateId;
	}
    
	
	public function setAggregateId($aggregateId) {
	    $this->aggregateId = $aggregateId;
	}
	
	public function getAttributes() {
	    return $this->attributes;
	}
	
	public function setAttributes($attributes) {
	    $this->attributes = $attributes;
	}
}
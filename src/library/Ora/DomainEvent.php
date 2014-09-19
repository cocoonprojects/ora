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
class DomainEvent {
	
	/**
	 * @ORM\Column(type="string")
	 * @var string
	 */
	protected $aggregateId;
	
	/**
	 * ORM\Column(type="json_array")
	 * @var array
	 */
//	protected $attributes;
	
	/**
	 * @ORM\Id @ORM\Column(type="integer")
	 * @ORM\GeneratedValue
	 * @var string
	 */
	private $id;
	
	/**
	 * @ORM\Column(type="datetime")
	 * @var DateTime
	 */
	private $firedAt;
	
	protected function __construct(DateTime $firedAt) {
		$this->firedAt = isset($firedAt) ? $firedAt: new DateTime();
	}
	
	public function getId() {
		return $this->id;
	}
	
	/**
	 * @return identifiedAggregate 
	 */
	public function getAggregateId() {
		return $this->aggregateId;
	}
	
	public function getFiredAt() {
		return $this->firedAt;
	}
	
}
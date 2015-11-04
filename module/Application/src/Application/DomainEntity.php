<?php
namespace Application;

use Prooph\EventSourcing\AggregateRoot;
use Rhumsaa\Uuid\Uuid;

/**
 * 
 * @author andreabandera
 *
 */
class DomainEntity extends AggregateRoot {
	
	/**
	 *  
	 * @var Uuid
	 */
	protected $id;

	/**
	 * @return Uuid
	 */
	public function getUuid() {
		return $this->id;
	}

	/**
	 * @return string
	 */
	public function getId() {
		return $this->id->toString();
	}

	/**
	 * @return string
	 */
	public function __toString() {
		return $this->getId();
	}

	/**
	 * @param DomainEntity $object
	 * @return bool
	 */
	public function equals(DomainEntity $object = null) {
		if(is_null($object)) {
			return false;
		}
		return $this->id->compareTo($object->getId()) === 0;
	}

	/**
	 * @return string
	 */
	protected function aggregateId() {
		return $this->id->toString();
	}
}
<?php
namespace Ora;

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
	
	public function getId() {
		return $this->id;
	}
	
	public function equals(DomainEntity $object = null) {
		if(is_null($object)) {
			return false;
		}
		return $this->id->compareTo($object->getId()) === 0;
	}
	
	protected function aggregateId() {
		return $this->id;
	}
	
}
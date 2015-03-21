<?php
namespace Ora;

use Zend\EventManager\EventManagerAwareInterface;
use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\EventManager;
use Prooph\EventSourcing\AggregateRoot;
use Rhumsaa\Uuid\Uuid;

/**
 * 
 * @author andreabandera
 *
 */
class DomainEntity extends AggregateRoot implements EventManagerAwareInterface {
	
	/**
	 *  
	 * @var Uuid
	 */
	protected $id;
	
	/**
	 * 
	 * @var EventManagerInterface
	 */
	private $events;
	
	public function getId() {
		return $this->id;
	}
	
	public function equals(DomainEntity $object = null) {
		if(is_null($object)) {
			return false;
		}
		return $this->id->compareTo($object->getId()) === 0;
	}
	
	public function setEventManager(EventManagerInterface $eventManager) {
		$this->events = $eventManager;
	}
	
	public function getEventManager()
	{
		if (!$this->events) {
			$this->setEventManager(new EventManager());
        }
		return $this->events;
	}
	
	protected function aggregateId() {
		return $this->id;
	}
	
}
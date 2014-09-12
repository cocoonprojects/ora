<?php
namespace Ora;

use Ora\EventStore\EventStore;

class Entity {
	
	/**
	 * @Id @Column(type="string") 
	 * @var string
	 */
	private $id;
	
	/**
	 * @Column(type="datetime")
	 * @var DateTime
	 */
	private $createdAt;
	
	private $es;
		
	public function __construct($id, \DateTime $createdAt, EventStore $es) {
		$this->id = $id;
		$this->createdAt = $createdAt;
		$this->es = $es;
	}
	
	public function getId() {
		return $this->id;
	}
	
	public function getCreatedAt() {
		return $this->createdAt;
	}
	
	public function rebuild($events) {
		foreach ($events as $e) {
			$this->apply($e);
		}
	}
	
	protected function appendToStream(DomainEvent $e) {
		return $this->es->appendToStream($e);
	}
	
	private function apply(DomainEvent $e) {
		$method = 'apply'.get_class($event);
		$this->$method($event);
	}
}
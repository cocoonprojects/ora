<?php
namespace Ora;

class Entity {
	
	private $id;
	
	private $createdAt;
		
	public function __construct($id, \DateTime $createdAt) {
		$this->id = $id;
		$this->createdAt = $createdAt;
	}
	
	public function getId() {
		return $this->id;
	}
	
	public function getCreatedAt() {
		return $this->createdAt;
	}
}
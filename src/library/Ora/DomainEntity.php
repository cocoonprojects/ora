<?php
namespace Ora;

use Doctrine\ORM\Mapping AS ORM;
use Prooph\EventSourcing\AggregateRoot;
use Rhumsaa\Uuid\Uuid;

/**
 * @ORM\MappedSuperclass
 * @author andreabandera
 *
 */
class DomainEntity extends AggregateRoot {
	
	/**
	 * @ORM\Id @ORM\Column(type="string") 
	 * @var Uuid
	 */
	protected $id;
	
	/**
	 * @ORM\Column(type="datetime")
	 * @var DateTime
	 */
	protected $createdAt;
	
	public function getId() {
		return $this->id;
	}
	
	public function getCreatedAt() {
		return $this->createdAt;
	}
	
	protected function aggregateId() {
		return $this->id;
	}
	
}
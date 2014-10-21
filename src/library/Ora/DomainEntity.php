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
	
	/**
	 * @ORM\ManyToOne(targetEntity="Ora\User\User")
	 */
	protected $createdBy;
	
    /**
     * @ORM\Column(type="datetime", nullable=TRUE)
     * @var datetime
     */
    protected $mostRecentEditAt;
    
    /**
     * @ORM\ManyToOne(targetEntity="Ora\User\User")
     */
    protected $mostRecentEditBy;
	
	public function getId() {
		return $this->id;
	}
	
	public function getCreatedAt() {
		return $this->createdAt;
	}
	
    public function getCreatedBy()
    {
        return $this->createdBy;
    }

    public function setMostRecentEditAt($datetime) {
        $this->mostRecentEditAt = $datetime;
    }
    
    public function getMostRecentEditAt() {
        return $this->mostRecentEditAt;
    }
    
    public function setMostRecentEditBy($user) {
        $this->mostRecentEditBy = $user;
    }
    
    public function getMostRecentEditBy() {
        return $this->mostRecentEditBy;
    }
    
	protected function aggregateId() {
		return $this->id;
	}
}
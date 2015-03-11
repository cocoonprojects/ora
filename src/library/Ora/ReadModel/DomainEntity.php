<?php
namespace Ora\ReadModel;

use Doctrine\ORM\Mapping AS ORM;
use Ora\User\User;

/**
 * @ORM\MappedSuperclass
 * @author andreabandera
 *
 */
class DomainEntity {
	
	/**
	 * @ORM\Id @ORM\Column(type="string") 
	 * @var string
	 */
	protected $id;
	
	/**
	 * @ORM\Column(type="datetime")
	 * @var \DateTime
	 */
	protected $createdAt;
	
	/**
	 * @ORM\ManyToOne(targetEntity="Ora\User\User")
     * @ORM\JoinColumn(name="createdBy_id", referencedColumnName="id", nullable=TRUE)
	 */
	protected $createdBy;
	
    public function __construct($id) {
    	$this->id = $id;
    }
	/**
	 * @return string
	 */
	public function getId() {
		return $this->id;
	}
	/**
	 * 
	 * @return \DateTime
	 */
	public function getCreatedAt() {
		return $this->createdAt;
	}
	/**
	 * 
	 * @param \DateTime $when
	 * @return \Ora\ReadModel\DomainEntity
	 */
	public function setCreatedAt(\DateTime $when) {
		$this->createdAt = $when;
		return $this;
	}
	/**
	 * 
	 * @return User
	 */
    public function getCreatedBy()
    {
        return $this->createdBy;
    }
    /**
     * 
     * @param User $user
     * @return \Ora\ReadModel\DomainEntity
     */
    public function setCreatedBy(User $user) {
    	$this->createdBy = $user;
    	return $this;
    }
	/**
	 * 
	 * @param DomainEntity $object
	 * @return boolean
	 */
    public function equals(DomainEntity $object = null) {
		if(is_null($object)) {
			return false;
		}
		return $this->id == $object->getId();
	}
	
}
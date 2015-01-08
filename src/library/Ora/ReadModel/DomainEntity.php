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
	 * @var DateTime
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
	
	public function getId() {
		return $this->id;
	}
	
	public function getCreatedAt() {
		return $this->createdAt;
	}
	
	public function setCreatedAt(\DateTime $when) {
		$this->createdAt = $when;
		return $this->createdAt;
	}
	
    public function getCreatedBy()
    {
        return $this->createdBy;
    }
    
    public function setCreatedBy(User $user) {
    	$this->createdBy = $user;
    	return $this->createdBy;
    }

    public function equals(DomainEntity $object = null) {
		if(is_null($object)) {
			return false;
		}
		return $this->id->compareTo($object->getId()) === 0;
	}
	
}
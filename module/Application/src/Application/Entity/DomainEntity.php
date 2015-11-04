<?php
namespace Application\Entity;

use Doctrine\ORM\Mapping AS ORM;

/**
 * @ORM\MappedSuperclass
 *
 */
abstract class DomainEntity {
	
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
	 * @ORM\ManyToOne(targetEntity="Application\Entity\User")
	 * @ORM\JoinColumn(name="createdBy_id", referencedColumnName="id", nullable=TRUE)
	 * @var BasicUser
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
		if(is_null($this->createdAt)) {
			$this->createdAt = new \DateTime();
		}
		return $this->createdAt;
	}
	/**
	 * 
	 * @param \DateTime $when
	 * @return $this
	 */
	public function setCreatedAt(\DateTime $when) {
		$this->createdAt = $when;
		return $this;
	}
	/**
	 * 
	 * @return BasicUser
	 */
	public function getCreatedBy()
	{
		return $this->createdBy;
	}
	/**
	 * 
	 * @param BasicUser $user
	 * @return $this
	 */
	public function setCreatedBy(BasicUser $user) {
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
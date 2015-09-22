<?php
namespace Application\Entity;

use Doctrine\ORM\Mapping AS ORM;

abstract class EditableEntity extends DomainEntity {
	
	/**
	 * @ORM\Column(type="datetime")
	 * @var \DateTime
	 */
	protected $mostRecentEditAt;
	
	/**
	 * @ORM\ManyToOne(targetEntity="Application\Entity\User")
	 * @ORM\JoinColumn(name="mostRecentEditBy_id", referencedColumnName="id", nullable=TRUE)
	 * @var BasicUser
	 */
	protected $mostRecentEditBy;
	/**
	 * 
	 * @return \DateTime
	 */
	public function getMostRecentEditAt() {
		return $this->mostRecentEditAt;
	}
	/**
	 * 
	 * @param \DateTime $when
	 * @return $this
	 */
	public function setMostRecentEditAt(\DateTime $when) {
		$this->mostRecentEditAt = $when;
		return $this;
	}
	/**
	 * 
	 * @return BasicUser
	 */
	public function getMostRecentEditBy() {
		return $this->mostRecentEditBy;
	}
	/**
	 * 
	 * @param BasicUser $user
	 * @return $this
	 */
	public function setMostRecentEditBy(BasicUser $user) {
		$this->mostRecentEditBy = $user;
		return $this;
	}

}
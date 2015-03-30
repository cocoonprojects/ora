<?php
namespace Ora\ReadModel;

use Doctrine\ORM\Mapping AS ORM;
use Ora\User\User;

abstract class EditableEntity extends DomainEntity {
	
	/**
	 * @ORM\Column(type="datetime")
	 * @var \DateTime
	 */
	protected $mostRecentEditAt;
	
	/**
	 * @ORM\ManyToOne(targetEntity="Ora\User\User")
	 * @ORM\JoinColumn(name="mostRecentEditBy_id", referencedColumnName="id", nullable=TRUE)
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
	 * @return \Ora\ReadModel\EditableEntity
	 */
	public function setMostRecentEditAt(\DateTime $when) {
		$this->mostRecentEditAt = $when;
		return $this;
	}
	/**
	 * 
	 * @return User
	 */
	public function getMostRecentEditBy() {
		return $this->mostRecentEditBy;
	}
	/**
	 * 
	 * @param User $user
	 * @return \Ora\ReadModel\EditableEntity
	 */
	public function setMostRecentEditBy(User $user) {
		$this->mostRecentEditBy = $user;
		return $this;
	}

}
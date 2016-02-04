<?php

namespace TaskManagement\Entity;

use Doctrine\ORM\Mapping AS ORM;
use Application\DomainEntity;
use Application\Entity\User;
use Application\DomainEntity;
use Application\Entity\BasicUser;
use Application\Entity\User;
use TaskManagement\Entity\Task;

/**
 * @ORM\Entity @ORM\Table(name="item_approvals")
 * @ORM\MappedSuperclass
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="type", type="string")
 */
abstract class Approval{

	/**
	 * @ORM\Id
	 * @ORM\Column(type="bigint")
	 * @ORM\GeneratedValue
	 * @var bigint
	 */
	protected $id;
	/**
	 * @ORM\ManyToOne(targetEntity="TaskManagement\Entity\Task" , inversedBy="approvals")
	 * @ORM\JoinColumn(name="item_id", referencedColumnName="id", onDelete="CASCADE")
	 */
	protected $item;
	
	/**
	 * @ORM\ManyToOne(targetEntity="Application\Entity\User")
	 * @ORM\JoinColumn(name="voter_id", referencedColumnName="id", onDelete="CASCADE")
	 */
	protected $voter;
	
	/**
	 * @ORM\Embedded(class="TaskManagement\Entity\Vote")
	 * @var Balance
	 */
	protected $vote;
	
	/**
	 * @ORM\Column(type="datetime")
	 * @var DateTime
	 */
	protected $createdAt;
	
	/**
	 * @ORM\ManyToOne(targetEntity="Application\Entity\User")
	 * @ORM\JoinColumn(name="createdBy_id", referencedColumnName="id")
	 * @var BasicUser
	 */
	protected $createdBy;
	
	/**
	 * @ORM\Column(type="datetime")
	 * @var datetime
	 */
	protected $mostRecentEditAt;
	
	/**
	 * @ORM\ManyToOne(targetEntity="Application\Entity\User")
	 * @ORM\JoinColumn(name="mostRecentEditBy_id", referencedColumnName="id")
	 * @var BasicUser
	 */
	protected $mostRecentEditBy;
	
	public function getId(){
		return $this->id;
	}
	
	public function getItem(){
		return $this->item;
	}
	
	public function getVoter(){
		return $this->voter;
	}
	
	public function getVote() {
		return $this->vote;
	}
	
	public function getCreatedAt() {
		return $this->createdAt;
	}
	
	public function setCreatedAt(\DateTime $when) {
		$this->createdAt = $when;
		return $this;
	}
		
	public function setItem(Task $item){
		$this->item=$item;
		return $this;	
	}
	
	public function setVoter(User $user){
		$this->voter=$user;
		return $this;
	}
	
	public function setCreatedBy(User $user){
		$this->createdBy = $user;
		return $this;
	}
	
	public function getCreatedBy(){
		return $this->createdBy;
	}
	
	public function setMostRecentEditBy(User $user){
		$this->mostRecentEditBy = $user;
		return $this;
	}
	
	public function  getMostRecentEditBy(){
		return $this->mostRecentEditBy;
	}
	
	public function setMostRecentEditAt(\DateTime $when){
		$this->mostRecentEditAt = $when;
		return $this;
	}
	
	public function  getMostRecentEditAt(){
		return $this->mostRecentEditAt;
	}

	

<?php

namespace FlowManagement\Entity;

use Doctrine\ORM\Mapping AS ORM;
use Application\Entity\User;
use Application\Entity\DomainEntity;
use Rhumsaa\Uuid\Uuid;
use FlowManagement\FlowCardInterface;

/**
 * @ORM\Entity
 * @ORM\Table(name="flowcards")
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="type", type="string")
 */
abstract class FlowCard extends DomainEntity implements FlowCardInterface{

	/**
	 * @ORM\ManyToOne(targetEntity="Application\Entity\User", inversedBy="flowcards")
	 * @ORM\JoinColumn(name="recipient_id", referencedColumnName="id", onDelete="CASCADE")
	 * @var User
	 */
	protected $recipient;

	/**
	 * @ORM\Column(type="datetime")
	 * @var \DateTime
	 */
	protected $mostRecentEditAt;

	/**
	 * @ORM\ManyToOne(targetEntity="Application\Entity\User")
	 * @ORM\JoinColumn(name="mostRecentEditBy_id", referencedColumnName="id")
	 * @var User
	 */
	protected $mostRecentEditBy;

	/**
	 * @ORM\Column(type="json_array", nullable=true)
	 * @var string
	 */
	private $contents;

	public function __construct($id, User $user){
		parent::__construct($id);
		$this->recipient = $user;
		$this->createdAt = $this->mostRecentEditAt = new \DateTime();
	}

	public function getRecipient(){
		return $this->recipient;
	}	

	public function getMostRecentEditAt() {
		return $this->mostRecentEditAt;
	}

	public function setMostRecentEditAt(\DateTime $when) {
		$this->mostRecentEditAt = $when;
		return $this;
	}

	public function getMostRecentEditBy() {
		return $this->mostRecentEditBy;
	}

	public function setMostRecentEditBy(User $user) {
		$this->mostRecentEditBy = $user;
		return $this;
	}

	public function setContents($contentKey, $contentValue){
		if(is_array($contentValue)){
			foreach ($contentValue as $key=>$value){
				$this->contents[$contentKey][$key] = $value;
			}
		}else{
			$this->contents[$contentKey] = $contentValue;
		}
		return $this;
	}

	public function getContents(){
		return $this->contents;
	}
}
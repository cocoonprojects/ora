<?php
namespace TaskManagement\Entity;

use Doctrine\ORM\Mapping AS ORM;
use Application\Entity\User;
use Zend\Crypt\PublicKey\Rsa\PublicKey;

/**
 * @ORM\Entity @ORM\Table(name="shares")
 *
 */
class Share {
	
	/**
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="AUTO")
	 * @ORM\Column(name="id", type="integer")
	 */
	private $id;
	
	/**
	 * 
	 * @ORM\ManyToOne(targetEntity="TaskMember", inversedBy="$shares")
	 * @ORM\JoinColumns({
	 * 		@ORM\JoinColumn(name="evaluator_id", referencedColumnName="member_id", onDelete="CASCADE", nullable=FALSE),
	 * 		@ORM\JoinColumn(name="task_id", referencedColumnName="task_id", onDelete="CASCADE", nullable=FALSE)
	 * })
	 * @var TaskMember
	 */
	private $evaluator;
	/**
	 * 
	 * @ORM\ManyToOne(targetEntity="TaskMember")
	 * @ORM\JoinColumns({
	 * 		@ORM\JoinColumn(name="valued_id", referencedColumnName="member_id", onDelete="CASCADE", nullable=FALSE),
	 * 		@ORM\JoinColumn(name="task_id", referencedColumnName="task_id", onDelete="CASCADE", nullable=FALSE)
	 * })
	 * @var TaskMember
	 */
	private $valued;
	/**
	 * @ORM\Column(type="float", precision=10, scale=4, nullable=true)
	 * @var float
	 */
	private $value;
	/**
	 * @ORM\Column(type="datetime")
	 * @var DateTime
	 */
	private $createdAt;
	
	public function __construct(TaskMember $evaluator, TaskMember $valued) {
		$this->evaluator = $evaluator;
		$this->valued = $valued;
	}
	
	public function setValue($value) {
		$this->value = $value;
		return $this;
	}
	
	public function getValue() {
		return $this->value;
	}
	
	public function getEvaluator() {
		return $this->evaluator;
	}
	
	public function getValued() {
		return $this->valued;
	}
	
	public function setCreatedAt(\DateTime $when) {
		$this->createdAt = $when;
		return $this;
	}
	
	public function getCreatedAt() {
		return $this->createdAt;
	}
}
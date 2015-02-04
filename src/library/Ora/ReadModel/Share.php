<?php
namespace Ora\ReadModel;

use Doctrine\ORM\Mapping AS ORM;
use Ora\User\User;
use Zend\Crypt\PublicKey\Rsa\PublicKey;

/**
 * @ORM\Entity @ORM\Table(name="shares")
 *
 */
class Share {
	
	/**
	 * @ORM\Id
	 * @ORM\ManyToOne(targetEntity="Ora\ReadModel\Task")
	 * @ORM\JoinColumn(name="task_id", referencedColumnName="id", onDelete="CASCADE")
	 * @var Task
	 */
	private $task;
	/**
	 * @ORM\Id
	 * @ORM\ManyToOne(targetEntity="Ora\User\User")
	 * @ORM\JoinColumn(name="evaluator_id", referencedColumnName="id", onDelete="CASCADE")
	 * @var User
	 */
	private $evaluator;
	/**
	 * @ORM\Id
	 * @ORM\ManyToOne(targetEntity="Ora\User\User")
	 * @ORM\JoinColumn(name="valued_id", referencedColumnName="id", onDelete="CASCADE")
	 * @var User
	 */
	private $valued;
	/**
	 * @ORM\Column(type="decimal", precision=10, scale=2, nullable=true)
	 * @var float
	 */
	private $value;
	/**
	 * @ORM\Column(type="datetime")
	 * @var DateTime
	 */
	private $createdAt;
	
	public function __construct(Task $task, User $evaluator, User $valued) {
		$this->task = $task;
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
	
	public function getTask() {
		return $this->task;
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
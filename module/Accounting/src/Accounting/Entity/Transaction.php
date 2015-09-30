<?php
namespace Accounting\Entity;

use Doctrine\ORM\Mapping AS ORM;
use Application\Entity\BasicUser;

/**
 * @ORM\Entity @ORM\Table(name="account_transactions")
 * @ORM\MappedSuperclass
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="type", type="string")
 *
 */
abstract class Transaction{

	/**
	 * @ORM\Id
	 * @ORM\Column(type="bigint")
	 * @ORM\GeneratedValue
	 * @var bigint
	 */
	protected $id;
	/**
	 * @ORM\ManyToOne(targetEntity="Account")
	 * @ORM\JoinColumn(name="payer_id", referencedColumnName="id")
	 * @var Account
	 */
	protected $payer;
	/**
	 * @ORM\ManyToOne(targetEntity="Account")
	 * @ORM\JoinColumn(name="payee_id", referencedColumnName="id")
	 * @var Account
	 */
	protected $payee;
	/**
	 * @ORM\Column(type="float")
	 * @var float
	 */
	protected $amount;
	/**
	 * @ORM\Column(type="string", nullable=true, length=256)
	 * @var string
	 */
	protected $description;
	/**
	 * @ORM\Column(type="float", scale=2)
	 * @var float
	 */
	protected $balance;
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
	
	public function getPayer() {
		return $this->payer;
	}
	
	public function getPayee() {
		return $this->payee;
	}
	
	public function setAmount($amount) {
		$this->amount = $amount;
		return $this;
	}
	
	public function getAmount() {
		return $this->amount;
	}
	
	public function setDescription($description) {
		$this->description = $description;
		return $this;
	}
	
	public function getDescription() {
		return $this->description;
	}
	
	public function setBalance($balance) {
		$this->balance = $balance;
		return $this;
	}
	
	public function getBalance() {
		return $this->balance;
	}
	
	public function getPayerName() {
		if($this->payer instanceof Account){
			return $this->payer->getName();
		}
		return null;
	}

	public function getPayeeName() {
		if($this->payee instanceof Account){
			return $this->payee->getName();
		}
		return null;
	}
}
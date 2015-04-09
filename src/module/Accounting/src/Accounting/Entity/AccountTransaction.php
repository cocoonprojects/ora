<?php
namespace Accounting\Entity;

use Doctrine\ORM\Mapping AS ORM;
use Application\Entity\DomainEntity;

/**
 * @ORM\Entity @ORM\Table(name="account_transactions")
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="type", type="string")
 *
 */
class AccountTransaction extends DomainEntity {
	
	/**
	 * @ORM\ManyToOne(targetEntity="Account", inversedBy="transactions")
	 * @ORM\JoinColumn(name="account_id", referencedColumnName="id")
	 * @var Account
	 */
	protected $account;
	/**
	 * @ORM\Column(type="float")
	 * @var float
	 */
	protected $amount;
	/**
	 * @ORM\Column(type="string", nullable=true)
	 * @var string
	 */
	protected $description;
	/**
	 * @ORM\Column(type="float", scale=2)
	 * @var float
	 */
	protected $balance;
	/**
	 * @ORM\Column(type="integer")
	 * @var integer
	 */
	private $number = 1;
	
	public function setAccount(Account $account) {
		$this->account = $account;
		return $this;
	}
	
	public function getAccount() {
		return $this->account;
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
		return null;
	}

	public function getPayeeName() {
		return null;
	}
	
	public function setNumber($number) {
		$this->number = $number;
		return $this;
	}
	
	public function getNumber() {
		return $this->number;
	}
}
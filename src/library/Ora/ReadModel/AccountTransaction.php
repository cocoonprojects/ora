<?php
namespace Ora\ReadModel;

use Doctrine\ORM\Mapping AS ORM;

/**
 * @ORM\Entity @ORM\Table(name="account_transactions")
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="type", type="string")
 * @author andreabandera
 *
 */
class AccountTransaction extends DomainEntity {
	
	/**
	 * @ORM\ManyToOne(targetEntity="Ora\ReadModel\Account", inversedBy="transactions")
	 * @ORM\JoinColumn(name="account_id", referencedColumnName="id", onDelete="CASCADE")
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
}
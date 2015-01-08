<?php
namespace Ora\ReadModel;

use Doctrine\ORM\Mapping AS ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Ora\User\User;

/**
 * @ORM\Entity @ORM\Table(name="accounts")
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="type", type="string")
 * @author andreabandera
 *
 */
class Account extends EditableEntity {
	
	/**
	 * @ORM\Embedded(class="Ora\ReadModel\Balance")
	 * @var Balance
	 */
	protected $balance;
	/**
	 * @ORM\ManyToMany(targetEntity="Ora\User\User")
	 * @ORM\JoinTable(name="account_holders", joinColumns={@ORM\JoinColumn(name="account_id", referencedColumnName="id", onDelete="CASCADE")},
	 * 		inverseJoinColumns={@ORM\JoinColumn(name="user_id", referencedColumnName="id", onDelete="CASCADE")}
	 * )
	 * @var ArrayCollection
	 */
	protected $holders;
	/**
	 * @ORM\OneToMany(targetEntity="Ora\ReadModel\AccountTransaction", mappedBy="account", cascade="persist")
	 * @ORM\OrderBy({"createdAt" = "DESC"})
	 * @var ArrayCollection
	 */
	protected $transactions;
	
	public function __construct($id, User $holder) {
		parent::__construct($id);
		$this->holders = new ArrayCollection();
		$this->holders->add($holder);
		$this->transactions = new ArrayCollection();
	}
	
	public function setBalance(Balance $balance) {
		$this->balance = $balance;
		return $this;
	}
	
	public function getBalance() {
		return $this->balance;
	}
	
	public function getHolders() {
		return $this->holders;
	}
	
	public function getTransactions() {
		return $this->transactions;
	}
	
	public function addTransaction(AccountTransaction $transaction) {
		$this->transactions->add($transaction);
		return $this;
	}
	
}
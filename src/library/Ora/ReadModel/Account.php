<?php
namespace Ora\ReadModel;

use Doctrine\ORM\Mapping AS ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Ora\User\User;
use Zend\Permissions\Acl\Resource\ResourceInterface;

/**
 * @ORM\Entity @ORM\Table(name="accounts")
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="type", type="string")
 * @author andreabandera
 *
 */
class Account extends EditableEntity implements ResourceInterface {
	
	/**
	 * @ORM\Embedded(class="Ora\ReadModel\Balance")
	 * @var Balance
	 */
	protected $balance;
	/**
	 * @ORM\ManyToMany(targetEntity="Ora\User\User", cascade={"PERSIST", "REMOVE"}, indexBy="id")
	 * @ORM\JoinTable(name="account_holders", joinColumns={@ORM\JoinColumn(name="account_id", referencedColumnName="id", onDelete="CASCADE")},
	 * 		inverseJoinColumns={@ORM\JoinColumn(name="user_id", referencedColumnName="id", onDelete="CASCADE")}
	 * )
	 * @var User[]
	 */
	protected $holders;
	/**
	 * @ORM\OneToMany(targetEntity="Ora\ReadModel\AccountTransaction", mappedBy="account", cascade="persist", fetch="LAZY")
	 * @ORM\OrderBy({"createdAt" = "DESC"})
	 * @var AccountTransaction[]
	 */
	protected $transactions;
	
	/**
	 * @ORM\OneToOne(targetEntity="Organization")
	 * @ORM\JoinColumn(name="organization_id", referencedColumnName="id", nullable=true, onDelete="CASCADE")
	 * @var Organization
	 */
	protected $organization;
	
	public function __construct($id) {
		parent::__construct($id);
		$this->holders = new ArrayCollection();
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
	
	public function addHolder(User $holder) {
		$this->holders->set($holder->getId(), $holder);
		return $this;
	}
	
	public function getTransactions() {
		return $this->transactions;
	}
	
	public function addTransaction(AccountTransaction $transaction) {
		$this->transactions->add($transaction);
		return $this;
	}
	
	public function isHeldBy(User $user) {
		return $this->holders->containsKey($user->getId());
	}
	
	public function getName() {
		if($this->holders->count() > 0) {
			$holder = $this->holders->first();
			return $holder->getFirstname() . ' ' . $holder->getLastname(); 
		}
		return null;
	}
	
	public function getResourceId(){
		return "Ora\Account";
	}
}
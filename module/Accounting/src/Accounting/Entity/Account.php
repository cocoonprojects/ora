<?php
namespace Accounting\Entity;

use Zend\Permissions\Acl\Resource\ResourceInterface;
use Doctrine\ORM\Mapping AS ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Application\Entity\EditableEntity;
use Application\Entity\User;
use People\Entity\Organization;
/**
 * @ORM\Entity 
 * @ORM\Table(name="accounts")
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="type", type="string")
 */
abstract class Account extends EditableEntity implements ResourceInterface {
	
	/**
	 * @ORM\Embedded(class="Accounting\Entity\Balance")
	 * @var Balance
	 */
	protected $balance;
	/**
	 * @ORM\ManyToMany(targetEntity="Application\Entity\User", cascade={"PERSIST", "REMOVE"}, indexBy="id")
	 * @ORM\JoinTable(name="account_holders", joinColumns={@ORM\JoinColumn(name="account_id", referencedColumnName="id", onDelete="CASCADE")},
	 * 		inverseJoinColumns={@ORM\JoinColumn(name="user_id", referencedColumnName="id", onDelete="CASCADE")}
	 * )
	 * @var User[]
	 */
	protected $holders;
	/**
	 * @ORM\OneToOne(targetEntity="People\Entity\Organization")
	 * @ORM\JoinColumn(name="organization_id", referencedColumnName="id", onDelete="CASCADE")
	 * @var Organization
	 */
	protected $organization;

	/**
	 * @param $id
	 * @param Organization $organization
	 */
	public function __construct($id, Organization $organization) {
		parent::__construct($id);
		$this->organization = $organization;
		$this->holders = new ArrayCollection();
	}

	/**
	 * @param Balance $balance
	 * @return $this
	 */
	public function setBalance(Balance $balance) {
		$this->balance = $balance;
		return $this;
	}

	/**
	 * @return Balance
	 */
	public function getBalance() {
		if(is_null($this->balance)) {
			$this->balance = new Balance(0, $this->getCreatedAt());
		}
		return $this->balance;
	}

	/**
	 * @return \Application\Entity\User[]
	 */
	public function getHolders() {
		return $this->holders->toArray();
	}

	/**
	 * @param User $holder
	 * @return $this
	 */
	public function addHolder(User $holder) {
		$this->holders->set($holder->getId(), $holder);
		return $this;
	}

	/**
	 * @param User $user
	 * @return bool
	 */
	public function isHeldBy(User $user) {
		return $this->holders->containsKey($user->getId());
	}

	/**
	 * @return Organization
	 */
	public function getOrganization() {
		return $this->organization;
	}

	/**
	 * @return string
	 */
	public function getOrganizationId() {
		return $this->getOrganization()->getId();
	}

	/**
	 * @return string
	 */
	public function getName() {
		if($this->holders->count() > 0) {
			$holder = $this->holders->first();
			return $holder->getFirstname() . ' ' . $holder->getLastname(); 
		}
		return null;
	}
}
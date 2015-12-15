<?php
namespace Accounting;

use People\Organization;
use Rhumsaa\Uuid\Uuid;
use Application\DomainEntity;
use Application\Entity\User;
use Application\DuplicatedDomainEntityException;
use Zend\Permissions\Acl\Resource\ResourceInterface;

class Account extends DomainEntity implements ResourceInterface
{
	/**
	 * @var string
	 */
	private $name;
	/**
	 * @var Uuid
	 */
	protected $organizationId;
	/**
	 * @var Balance
	 */
	private $balance;
	/**
	 * @var map($id, name)
	 */
	private $holders = [];

	/**
	 * @param Organization $organization
	 * @param User $createdBy
	 * @param string $name
	 * @return Account
	 */
	public static function create(Organization $organization, User $createdBy, $name = null) {
		$rv = new static();
		// At creation time the balance is 0
		$event = AccountCreated::occur(Uuid::uuid4()->toString(), [
				'balance' => 0,
				'name' => is_null($name) ? $createdBy->getFirstname().' '.$createdBy->getLastname() : $name,
				'by' => $createdBy->getId(),
				'organization' => $organization->getId(),
		]);
		$rv->recordThat($event);
		$rv->addHolder($createdBy, $createdBy);
		return $rv;
	}

	/**
	 * @return string
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * @return Balance
	 */
	public function getBalance() {
		return $this->balance;
	}

	/**
	 * @return map
	 */
	public function getHolders() {
		return $this->holders;
	}

	/**
	 * @return Uuid
	 */
	public function getOrganizationId() {
		return $this->organizationId;
	}

	/**
	 * @param $amount
	 * @param User $holder
	 * @param $description
	 * @return array
	 */
	public function deposit($amount, User $holder, $description) {
		if ($amount <= 0) {
			throw new IllegalAmountException($amount);
		}

		$event = CreditsDeposited::occur($this->id->toString(), [
				'amount' => $amount,
				'description' => $description,
				'balance' => $this->balance->getValue() + $amount,
				'prevBalance' => $this->balance->getValue(),
				'by' => $holder->getId(),
		]);
		$this->recordThat($event);

		return [
				'account' => [
						'id' => $this->getId()
				],
				'amount' => $amount,
				'description' => $description,
				'balance' => $event->payload()['balance'],
				'date' => date_format($event->occurredOn(), 'c'),
				'payer' => $holder->getFirstname().' '.$holder->getLastname(),
				'type' => 'Deposit'
		];
	}

	/**
	 * @param $amount
	 * @param User $holder
	 * @param $description
	 * @return array
	 */
	public function withdraw($amount, User $holder, $description) {
		if ($amount >= 0) {
			throw new IllegalAmountException($amount);
		}
//		if(!array_key_exists($holder->getId(), $this->holders)) {
//			throw new IllegalPayerException();
//		}
		$event = CreditsWithdrawn::occur($this->id->toString(), [
				'amount' => $amount,
				'description' => $description,
				'balance' => $this->balance->getValue() + $amount,
				'prevBalance' => $this->balance->getValue(),
				'by' => $holder->getId(),
		]);
		$this->recordThat($event);
		return [
				'account' => [
						'id' => $this->getId()
				],
				'amount' => $amount,
				'description' => $description,
				'balance' => $event->payload()['balance'],
				'date' => date_format($event->occurredOn(), 'c'),
				'payee' => $holder->getFirstname().' '.$holder->getLastname(),
				'type' => 'Withdrawal'
		];
	}

	/**
	 * @param $amount
	 * @param Account $payer
	 * @param $description
	 * @param User $by
	 * @return array
	 */
	public function transferIn($amount, Account $payer, $description, User $by) {
		if ($amount <= 0) {
			throw new IllegalAmountException($amount);
		}
		$event = IncomingCreditsTransferred::occur($this->id->toString(), [
				'amount' => $amount,
				'description' => $description,
				'payer' => $payer->getId(),
				'balance' => $this->balance->getValue() + $amount,
				'prevBalance' => $this->balance->getValue(),
				'by' => $by->getId(),
		]);
		$this->recordThat($event);
		return [
				'account' => [
						'id' => $this->getId()
				],
				'amount' => $amount,
				'description' => $description,
				'balance' => $event->payload()['balance'],
				'date' => date_format($event->occurredOn(), 'c'),
				'payer' => $payer->getName(),
				'type' => 'IncomingTransfer'
		];
	}

	/**
	 * @param $amount
	 * @param Account $payee
	 * @param $description
	 * @param User $by
	 * @return array
	 */
	public function transferOut($amount, Account $payee, $description, User $by) {
		if ($amount >= 0) {
			throw new IllegalAmountException($amount);
		}
		$event = OutgoingCreditsTransferred::occur($this->id->toString(), [
				'amount' => $amount,
				'description' => $description,
				'payee' => $payee->getId(),
				'balance' => $this->balance->getValue() + $amount,
				'prevBalance' => $this->balance->getValue(),
				'by' => $by->getId(),
		]);
		$this->recordThat($event);
		return [
				'account' => [
						'id' => $this->getId()
				],
				'amount' => $amount,
				'description' => $description,
				'balance' => $event->payload()['balance'],
				'date' => date_format($event->occurredOn(), 'c'),
				'payee' => $payee->getName(),
				'type' => 'OutgoingTransfer'
		];
	}

	/**
	 * @param User $holder
	 * @param User $by
	 * @return $this
	 */
	public function addHolder(User $holder, User $by) {
		if(array_key_exists($holder->getId(), $this->holders)) {
			throw new DuplicatedDomainEntityException($this, $holder);
		}
		$this->recordThat(HolderAdded::occur($this->id->toString(), array(
				'id' => $holder->getId(),
				'firstname' => $holder->getFirstname(),
				'lastname' => $holder->getLastname(),
				'by' => $by->getId(),
		)));
		return $this;
	}

	/**
	 * @param User $user
	 * @return bool
	 */
	public function isHeldBy(User $user) {
		return array_key_exists($user->getId(), $this->holders);
	}

	/**
	 * Returns the string identifier of the Resource
	 *
	 * @return string
	 */
	public function getResourceId()
	{
		return 'Ora\PersonalAccount';
	}

	protected function whenAccountCreated(AccountCreated $event) {
		$this->id = Uuid::fromString($event->aggregateId());
		$this->organizationId = Uuid::fromString($event->payload()['organization']);
		$this->balance = new Balance(0, $event->occurredOn());
		if(isset($event->payload()['name'])) {
			$this->name = $event->payload()['name'];
		}
	}
	
	protected function whenHolderAdded(HolderAdded $e) {
		$id = $e->payload()['id'];
		$firstname = $e->payload()['firstname'];
		$lastname = $e->payload()['lastname'];
		$this->holders[$id] = $firstname.' '.$lastname;
	}
	
	protected function whenCreditsDeposited(CreditsDeposited $e) {
		$current = $this->getBalance()->getValue();
		$value = $e->payload()['amount'];
		$this->balance = new Balance($current + $value, $e->occurredOn());
	}

	protected function whenCreditsWithdrawn(CreditsWithdrawn $e) {
		$current = $this->getBalance()->getValue();
		$value = $e->payload()['amount'];
		$this->balance = new Balance($current + $value, $e->occurredOn());
	}

	protected function whenIncomingCreditsTransferred(IncomingCreditsTransferred $e) {
		$current = $this->getBalance()->getValue();
		$value = $e->payload()['amount'];
		$this->balance = new Balance($current + $value, $e->occurredOn());
	}
	
	protected function whenOutgoingCreditsTransferred(OutgoingCreditsTransferred $e) {
		$current = $this->getBalance()->getValue();
		$value = $e->payload()['amount'];
		$this->balance = new Balance($current + $value, $e->occurredOn());
	}
}

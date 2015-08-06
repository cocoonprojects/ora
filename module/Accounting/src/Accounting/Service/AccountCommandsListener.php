<?php
namespace Accounting\Service;

use Accounting\Entity\PersonalAccount;
use Accounting\Entity\Account;
use Accounting\Entity\Balance;
use Accounting\Entity\Deposit;
use Accounting\Entity\OrganizationAccount;
use Accounting\Entity\IncomingTransfer;
use Accounting\Entity\OutgoingTransfer;
use Application\Entity\User;
use Application\Service\ReadModelProjector;
use People\Entity\Organization;
use Prooph\EventStore\Stream\StreamEvent;

class AccountCommandsListener extends ReadModelProjector {
	
	protected function onAccountCreated(StreamEvent $event) {
		$id = $event->metadata()['aggregate_id'];
		$organizationId = $event->payload()['organization'];

		$organization = $this->entityManager->find(Organization::class, $organizationId);

		$entity = $event->metadata()['aggregate_type'] == 'Accounting\OrganizationAccount' ? new OrganizationAccount($id, $organization) : new PersonalAccount($id, $organization);

		$createdBy = $this->entityManager->find(User::class, $event->payload()['by']);
		$entity->setCreatedAt($event->occurredOn());
		$entity->setCreatedBy($createdBy);
		$entity->setMostRecentEditAt($event->occurredOn());
		$entity->setMostRecentEditBy($createdBy);
		$balance = new Balance($event->payload()['balance'], $event->occurredOn());
		$entity->setBalance($balance);
		$this->entityManager->persist($entity);
	}
	
	protected function onHolderAdded(StreamEvent $event) {
		$id = $event->metadata()['aggregate_id'];
		$entity = $this->entityManager->find(Account::class, $id);
		
		$holder = $this->entityManager->find(User::class, $event->payload()['id']);
		$entity->addHolder($holder);
		$entity->setMostRecentEditAt($event->occurredOn());
		
		$createdBy = $this->entityManager->find(User::class, $event->payload()['by']);
		$entity->setMostRecentEditBy($createdBy);
		$this->entityManager->persist($entity);
	}
	
	protected function onCreditsDeposited(StreamEvent $event) {
		$id = $event->metadata()['aggregate_id'];
		$entity = $this->entityManager->find(Account::class, $id);
		
		$amount = $event->payload()['amount'];
		$balance = $event->payload()['balance'];
		
		$payer = $this->entityManager->find(User::class, $event->payload()['payer']);

		$transaction = new Deposit($event->eventId());
		$transaction->setAccount($entity)
			->setAmount($amount)
			->setBalance($balance)
			->setDescription($event->payload()['description'])
			->setCreatedAt($event->occurredOn())
			->setCreatedBy($payer)
			->setNumber($event->version());
		$entity->addTransaction($transaction);
		
		$balance = new Balance($transaction->getBalance(), $event->occurredOn());
		$entity->setBalance($balance);
		$entity->setMostRecentEditAt($event->occurredOn());
		$entity->setMostRecentEditBy($payer);
		$this->entityManager->persist($entity);
	}

	protected function onIncomingCreditsTransferred(StreamEvent $event) {
		$id = $event->metadata()['aggregate_id'];
		$entity = $this->entityManager->find(Account::class, $id);
		
		$payerId = $event->payload()['payer'];
		$payer = $this->entityManager->find(Account::class, $payerId);
		
		$amount = $event->payload()['amount'];

		$createdBy = $this->entityManager->find(User::class, $event->payload()['by']);

		$transaction = new IncomingTransfer($event->eventId());
		$transaction->setAccount($entity)
			->setPayer($payer)
			->setAmount($amount)
			->setBalance($entity->getBalance()->getValue() + $amount)
			->setDescription($event->payload()['description'])
			->setCreatedAt($event->occurredOn())
			->setCreatedBy($createdBy)
			->setNumber($event->version());
		$entity->addTransaction($transaction);
		
		$balance = new Balance($transaction->getBalance(), $event->occurredOn());
		$entity->setBalance($balance);
		$entity->setMostRecentEditAt($event->occurredOn());
		$entity->setMostRecentEditBy($createdBy);
		$this->entityManager->persist($entity);
	}
	
	protected function onOutgoingCreditsTransferred(StreamEvent $event) {
		$id = $event->metadata()['aggregate_id'];
		$entity = $this->entityManager->find(Account::class, $id);
		
		$payeeId = $event->payload()['payee'];
		$payee = $this->entityManager->find(Account::class, $payeeId);
		
		$amount = $event->payload()['amount'];

		$createdBy = $this->entityManager->find(User::class, $event->payload()['by']);

		$transaction = new OutgoingTransfer($event->eventId());
		$transaction->setAccount($entity)
			->setPayee($payee)
			->setAmount($amount)
			->setBalance($entity->getBalance()->getValue() + $amount)
			->setDescription($event->payload()['description'])
			->setCreatedAt($event->occurredOn())
			->setCreatedBy($createdBy)
			->setNumber($event->version());
		$entity->addTransaction($transaction);
		
		$balance = new Balance($transaction->getBalance(), $event->occurredOn());
		$entity->setBalance($balance);
		$entity->setMostRecentEditAt($event->occurredOn());
		$entity->setMostRecentEditBy($createdBy);
		$this->entityManager->persist($entity);
	}
	
	protected function getPackage() {
		return 'Accounting';
	}
}

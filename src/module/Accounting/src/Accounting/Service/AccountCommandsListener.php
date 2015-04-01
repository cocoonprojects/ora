<?php
namespace Accounting\Service;

use Prooph\EventStore\Stream\StreamEvent;
use Ora\Service\SyncReadModelListener;
use Ora\ReadModel\Balance;
use Ora\ReadModel\Account;
use Ora\ReadModel\OrganizationAccount;
use Ora\ReadModel\Deposit;
use Ora\ReadModel\IncomingTransfer;
use Ora\ReadModel\OutgoingTransfer;

class AccountCommandsListener extends SyncReadModelListener {
	
	protected function onAccountCreated(StreamEvent $event) {
		$id = $event->metadata()['aggregate_id'];
		$createdBy = $this->entityManager->find('Ora\User\User', $event->payload()['by']);
		if(isset($event->payload()['organization'])) {
			$orgId = $event->payload()['organization'];
			$organization = $this->entityManager->find('Ora\ReadModel\Organization', $orgId);
		}
		$entity = isset($organization) ? new OrganizationAccount($id, $organization) : new Account($id);
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
		$entity = $this->entityManager->find('Ora\\ReadModel\\Account', $id);
		
		$holder = $this->entityManager->find('Ora\User\User', $event->payload()['id']);
		$entity->addHolder($holder);
		$entity->setMostRecentEditAt($event->occurredOn());
		
		$createdBy = $this->entityManager->find('Ora\User\User', $event->payload()['by']);
		$entity->setMostRecentEditBy($createdBy);
		$this->entityManager->persist($entity);
	}
	
	protected function onCreditsDeposited(StreamEvent $event) {
		$id = $event->metadata()['aggregate_id'];
		$entity = $this->entityManager->find('Ora\\ReadModel\\Account', $id);
		
		$amount = $event->payload()['amount'];
		$balance = $event->payload()['balance'];
		
		$payer = $this->entityManager->find('Ora\User\User', $event->payload()['payer']);

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
		$entity = $this->entityManager->find('Ora\\ReadModel\\Account', $id);
		
		$payerId = $event->payload()['payer'];
		$payer = $this->entityManager->find('Ora\\ReadModel\\Account', $payerId);
		
		$amount = $event->payload()['amount'];

		$createdBy = $this->entityManager->find('Ora\User\User', $event->payload()['by']);

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
		$entity = $this->entityManager->find('Ora\ReadModel\Account', $id);
		
		$payeeId = $event->payload()['payee'];
		$payee = $this->entityManager->find('Ora\ReadModel\Account', $payeeId);
		
		$amount = $event->payload()['amount'];

		$createdBy = $this->entityManager->find('Ora\User\User', $event->payload()['by']);

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

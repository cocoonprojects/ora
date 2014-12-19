<?php
namespace Accounting\Service;

use Doctrine\ORM\EntityManager;
use Prooph\EventStore\PersistenceEvent\PostCommitEvent;
use Prooph\EventStore\EventStore;
use Prooph\EventStore\Stream\StreamEvent;
use Ora\ReadModel\Task;
use Ora\ReadModel\Balance;
use Ora\ReadModel\Account;
use Ora\ReadModel\Deposit;

class AccountListener
{
	/**
	 * 
	 * @var EntityManager
	 */
	private $entityManager;
	
	public function __construct(EntityManager $entityManager) {
		$this->entityManager = $entityManager;
	}
	
	public function attach(EventStore $eventStore) {
		$eventStore->getPersistenceEvents()->attach('commit.post', array($this, 'postCommit'));
	}
	
	public function postCommit(PostCommitEvent $event) {
		foreach ($event->getRecordedEvents() as $streamEvent) {
			$type = $streamEvent->metadata()['aggregate_type'];

			$handler = $this->determineEventHandlerMethodFor($streamEvent);
			if (! method_exists($this, $handler)) {
				continue;
// 				throw new \RuntimeException(sprintf(
// 						"Missing event handler method %s for aggregate root %s",
// 						$handler,
// 						get_class($this)
// 				));
			}
			
			$this->{$handler}($streamEvent);				
		}
 		$this->entityManager->flush();
	}
	
	protected function onAccountCreated(StreamEvent $event) {
		$id = $event->metadata()['aggregate_id'];
		$createdBy = $this->entityManager->find('Ora\User\User', $event->payload()['holders'][0]);
		if(isset($event->payload()['organization'])) {
			$orgId = $event->payload()['organization'];
			$organization = $this->entityManager->find('Ora\ReadModel\Organization', $orgId);
		}
		$entity = isset($organization) ? new OrganizationAccount($id, $createdBy, $organization) : new Account($id, $createdBy);
		$entity->setCreatedAt($event->occurredOn());
		$entity->setCreatedBy($createdBy);
		$entity->setMostRecentEditAt($event->occurredOn());
		$entity->setMostRecentEditBy($createdBy);
		$balance = new Balance($event->payload()['balance'], $event->occurredOn());
		$entity->setBalance($balance);
		$this->entityManager->persist($entity);
	}
	
	protected function onCreditsDeposited(StreamEvent $event) {
		$id = $event->metadata()['aggregate_id'];
		$entity = $this->entityManager->find('Ora\\ReadModel\\Account', $id);
		
		$transaction = new Deposit($event->eventId());
		$transaction->setAccount($entity);
		$amount = $event->payload()['amount'];
		$transaction->setAmount($amount);
		$transaction->setBalance($entity->getBalance()->getValue() + $amount);
		$transaction->setDescription($event->payload()['description']);
		$transaction->setCreatedAt($event->occurredOn());
		$payer = $this->entityManager->find('Ora\User\User', $event->payload()['payer']);
		$transaction->setCreatedBy($payer);
		$entity->addTransaction($transaction);
		
		$balance = new Balance($transaction->getBalance(), $event->occurredOn());
		$entity->setBalance($balance);
		$entity->setMostRecentEditAt($event->occurredOn());
		$entity->setMostRecentEditBy($payer);
		$this->entityManager->persist($entity);
	}
	
	protected function determineEventHandlerMethodFor(StreamEvent $e)
    {
        return 'on' . join('', array_slice(explode('\\', $e->eventName()), -1));
    }
}

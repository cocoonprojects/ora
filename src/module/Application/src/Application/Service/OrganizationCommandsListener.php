<?php
namespace Application\Service;

use Prooph\EventStore\Stream\StreamEvent;
use Ora\Service\SyncReadModelListener;
use Ora\ReadModel\Account;
use Ora\ReadModel\Organization;
use Ora\ReadModel\OrganizationMembership;

class OrganizationCommandsListener extends SyncReadModelListener {
	
	protected function onOrganizationCreated(StreamEvent $event) {
		$id = $event->metadata()['aggregate_id'];
		$createdBy = $this->entityManager->find('Ora\User\User', $event->payload()['by']);

		$entity = new Organization($id);
		$entity->setCreatedAt($event->occurredOn());
		$entity->setCreatedBy($createdBy);
		$entity->setMostRecentEditAt($event->occurredOn());
		$entity->setMostRecentEditBy($createdBy);
		$this->entityManager->persist($entity);
	}
	
	protected function onOrganizationUpdated(StreamEvent $event) {
		if(isset($event->payload()['name'])) {
			$id = $event->metadata()['aggregate_id'];
			$entity = $this->entityManager->find('Ora\ReadModel\Organization', $id);
			$updatedBy = $this->entityManager->find('Ora\User\User', $event->payload()['by']);
				
			$entity->setName($event->payload()['name']);
			$entity->setMostRecentEditAt($event->occurredOn());
			$entity->setMostRecentEditBy($updatedBy);
			$this->entityManager->persist($entity);
		}
	}
	
	protected function onOrganizationMemberAdded(StreamEvent $event) {
		$id = $event->metadata()['aggregate_id'];
		$organization = $this->entityManager->find('Ora\ReadModel\Organization', $id);
		
		$user = $this->entityManager->find('Ora\User\User', $event->payload()['userId']);
		$createdBy = $this->entityManager->find('Ora\User\User', $event->payload()['by']);

		$m = new OrganizationMembership($user, $organization);
		$m->setRole($event->payload()['role'])
		  ->setCreatedAt($event->occurredOn())
		  ->setCreatedBy($createdBy)
		  ->setMostRecentEditAt($event->occurredOn())
		  ->setMostRecentEditBy($createdBy);
		$this->entityManager->persist($m);
	}
	
	protected function onOrganizationMemberRemoved(StreamEvent $event) {
		
		$membership = $this->entityManager
			 			   ->getRepository('Ora\ReadModel\OrganizationMembership')
						   ->findBy(array(
						   		'member' => $event->payload()['userId'],
						   		'organization' => $event->metadata()['aggregate_id']
						   ));
		$this->entityManager->remove($membership);
	}
	
	protected function getPackage() {
		return 'Ora\\Organization\\';
	}
}
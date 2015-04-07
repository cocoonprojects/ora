<?php
namespace Application\Service;

use Prooph\EventStore\Stream\StreamEvent;
use Ora\Service\SyncReadModelListener;
use Application\Entity\User;
use Application\Entity\Organization;
use Application\Entity\OrganizationMembership;

class OrganizationCommandsListener extends SyncReadModelListener {
	
	protected function onOrganizationCreated(StreamEvent $event) {
		$id = $event->metadata()['aggregate_id'];
		$createdBy = $this->entityManager->find(User::class, $event->payload()['by']);

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
			$entity = $this->entityManager->find(Organization::class, $id);
			$updatedBy = $this->entityManager->find(User::class, $event->payload()['by']);
				
			$entity->setName($event->payload()['name']);
			$entity->setMostRecentEditAt($event->occurredOn());
			$entity->setMostRecentEditBy($updatedBy);
			$this->entityManager->persist($entity);
		}
	}
	
	protected function onOrganizationMemberAdded(StreamEvent $event) {
		$id = $event->metadata()['aggregate_id'];
		$organization = $this->entityManager->find(Organization::class, $id);
		
		$user = $this->entityManager->find(User::class, $event->payload()['userId']);
		$createdBy = $this->entityManager->find(User::class, $event->payload()['by']);

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
			 			   ->getRepository(OrganizationMembership::class)
						   ->findBy(array(
						   		'member' => $event->payload()['userId'],
						   		'organization' => $event->metadata()['aggregate_id']
						   ));
		$this->entityManager->remove($membership);
	}
	
	protected function getPackage() {
		return 'Application';
	}
}
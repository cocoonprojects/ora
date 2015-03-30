<?php
namespace TaskManagement\Service;

use Prooph\EventStore\Stream\StreamEvent;
use Ora\Service\SyncReadModelListener;
use Ora\ReadModel\Stream;

class StreamCommandsListener extends SyncReadModelListener {
	
	protected function onStreamCreated(StreamEvent $event) {
		$id = $event->metadata()['aggregate_id'];
		$organizationId = $event->payload()['organizationId'];
		$organization = $this->entityManager->find('Ora\ReadModel\Organization', $organizationId);
		if(is_null($organization)) {
			return;
		}
		$createdBy = $this->entityManager->find('Ora\User\User', $event->payload()['by']);
		
		$stream = new Stream($id);
		$stream->setOrganization($organization)
			   ->setCreatedAt($event->occurredOn())
			   ->setCreatedBy($createdBy)
			   ->setMostRecentEditAt($event->occurredOn())
			   ->setMostRecentEditBy($createdBy);
		$this->entityManager->persist($stream);
	}

	protected function onStreamUpdated(StreamEvent $event) {
		if(isset($event->payload()['subject'])) {
			$id = $event->metadata()['aggregate_id'];
			$stream = $this->entityManager->find('Ora\ReadModel\Stream', $id);
			if(is_null($stream)) {
				return;
			}
			$updatedBy = $this->entityManager->find('Ora\User\User', $event->payload()['by']);

			$stream->setSubject($event->payload()['subject']);
			$stream->setMostRecentEditAt($event->occurredOn());
			$stream->setMostRecentEditBy($updatedBy);
			$this->entityManager->persist($stream);
		}
	}

	protected function onOrganizationChanged(StreamEvent $event) {
		$id = $event->metadata()['aggregate_id'];
		$entity = $this->entityManager->find('Ora\ReadModel\Task', $id);
		if(is_null($entity)) {
			return;
		}
		$organizationId = $event->payload()['organizationId'];
		$organization = $this->entityManager->find('Ora\ReadModel\Organization', $organizationId);
		if(is_null($organization)) {
			return;
		}
		$updatedBy = $this->entityManager->find('Ora\User\User', $event->payload()['by']);
		
		$entity->setOrganization($organization);
		$entity->setMostRecentEditAt($event->occurredOn());
		$entity->setMostRecentEditBy($updatedBy);
		$this->entityManager->persist($entity);
	}

	protected function getPackage() {
		return 'Ora\\StreamManagement\\';
	}
}
<?php

namespace FlowManagement\Service;

use Application\Service\UserService;
use Application\Service\ReadModelProjector;
use Prooph\EventStore\Stream\StreamEvent;
use Application\Entity\User;
use FlowManagement\Entity\LazyMajorityVoteCard;
use FlowManagement\FlowCardInterface;

class CardCommandsListener extends ReadModelProjector {
	
	public function onFlowCardCreated(StreamEvent $event) {
		$createdBy = $this->entityManager->find(User::class, $event->payload()['by']);
		$recipient = $this->entityManager->find(User::class, $event->payload()['to']);
		$entity = $this->cardFactory($recipient, $event);
		if(!is_null($entity)){
			$entity->setCreatedAt($event->occurredOn());
			$entity->setCreatedBy($createdBy);
			$entity->setMostRecentEditAt($event->occurredOn());
			$entity->setMostRecentEditBy($createdBy);
			$this->entityManager->persist($entity);
		}
	}
	
	private function cardFactory(User $recipient, StreamEvent $event){
		$id = $event->metadata()['aggregate_id'];
		$content = $event->payload()['content'];
		$type = $event->metadata()['aggregate_type'];
		switch ($type){
			case 'FlowManagement\LazyMajorityVoteCard':
				$entity = new LazyMajorityVoteCard($id, $recipient);
				$entity->setContent(FlowCardInterface::LAZY_MAJORITY_VOTE, $content);
				break;
			default:
				$entity = null;
		}
		
		return $entity;
	}
	
	protected function getPackage() {
		return 'FlowManagement';
	}
}
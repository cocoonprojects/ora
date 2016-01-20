<?php

namespace FlowManagement\Service;

use Application\Service\UserService;
use Application\Service\ReadModelProjector;
use Prooph\EventStore\Stream\StreamEvent;
use Application\Entity\User;
use FlowManagement\Entity\LazyMajorityVoteCard;
use FlowManagement\FlowCardInterface;

class CardCommandsListener extends ReadModelProjector {
	
	public function whenFlowCardCreated(StreamEvent $event) {
		$id = $event->metadata()['aggregate_id'];
		$content = $event->payload()['content'];
		$createdBy = $this->entityManager->find(User::class, $event->payload()['by']);
		$recipient = $this->entityManager->find(User::class, $event->payload()['to']);
		$type = $event->metadata()['aggregate_type'];
		$entity = $this->cardFactory($type, $id, $recipient);
		if(!is_null($entity)){
			$entity->setContents(FlowCardInterface::LAZY_MAJORITY_VOTE, $content);
			$entity->setCreatedAt($event->occurredOn());
			$entity->setCreatedBy($createdBy);
			$entity->setMostRecentEditAt($event->occurredOn());
			$entity->setMostRecentEditBy($createdBy);
			$this->entityManager->persist($entity);
		}
	}
	
	private function cardFactory($type, $id, User $recipient){
		switch ($type){
			case 'FlowManagement\LazyMajorityVoteCard':
				$entity = new LazyMajorityVoteCard($id, $recipient);
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
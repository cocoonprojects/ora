<?php

namespace FlowManagement\Service;

use Application\Service\UserService;
use Application\Service\ReadModelProjector;
use Prooph\EventStore\Stream\StreamEvent;
use Application\Entity\User;
use FlowManagement\Entity\VoteIdeaCard;
use FlowManagement\Entity\VoteCompletedItemCard;
use FlowManagement\FlowCardInterface;
use TaskManagement\Entity\Task;
use FlowManagement\Entity\FlowCard;

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
	
	public function onFlowCardHidden(StreamEvent $event) {
		$id = $event->metadata()['aggregate_id'];
		$entity = $this->entityManager->find(FlowCard::class, $id);
		if(!is_null($entity)){
			$entity->setMostRecentEditAt($event->occurredOn());
			$entity->hide();
			$this->entityManager->persist($entity);
		}
	}

	private function cardFactory(User $recipient, StreamEvent $event){
		$id = $event->metadata()['aggregate_id'];
		$content = $event->payload()['content'];
		$type = $event->metadata()['aggregate_type'];
		switch ($type){
			case 'FlowManagement\VoteIdeaCard':
				$entity = new VoteIdeaCard($id, $recipient);
				$idea = $this->entityManager->find(Task::class, $event->payload()['item']);
				if(!is_null($idea)){
					$entity->setItem($idea);
				} 
				$entity->setContent(FlowCardInterface::VOTE_IDEA_CARD, $content);
				break;
			case 'FlowManagement\VoteCompletedItemCard':
				$entity = new VoteCompletedItemCard($id, $recipient);
				$idea = $this->entityManager->find(Task::class, $event->payload()['item']);
				if(!is_null($idea)){
					$entity->setItem($idea);
				} 
				$entity->setContent(FlowCardInterface::VOTE_COMPLETED_ITEM_CARD, $content);
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
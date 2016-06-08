<?php

namespace Kanbanize\Service;

use Application\Entity\User;
use Application\Service\ReadModelProjector;
use Doctrine\ORM\EntityManager;
use Kanbanize\KanbanizeStream;
use Kanbanize\Entity\KanbanizeStream as ReadModelKanbanizeStream;
use People\Entity\Organization;
use Prooph\EventStore\Stream\StreamEvent;
use TaskManagement\StreamCreated;
use TaskManagement\StreamUpdated;
use TaskManagement\Entity\Stream;
use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\Event;
use Zend\Mvc\Application;

class StreamCommandsListener extends ReadModelProjector{

	public function attach(EventManagerInterface $events){
		parent::attach($events);
		
		$this->listeners[] = $events->getSharedManager()->attach(Application::class, StreamCreated::class,
			function(Event $event) {
				$streamEvent = $event->getTarget();
				$id = $streamEvent->metadata()['aggregate_id'];
				if($streamEvent->metadata()['aggregate_type'] == KanbanizeStream::class){
					$organizationId = $streamEvent->payload()['organizationId'];
					$organization = $this->entityManager->find(Organization::class, $organizationId);
					if(is_null($organization)) {
						return;
					}
					$createdBy = $this->entityManager->find(User::class, $streamEvent->payload()['by']);
					$stream = new ReadModelKanbanizeStream($id, $organization);
					$stream->setBoardId($streamEvent->payload()['boardId'])
						->setProjectId($streamEvent->payload()['projectId'])
						->setCreatedAt($streamEvent->occurredOn())
						->setCreatedBy($createdBy)
						->setMostRecentEditAt($streamEvent->occurredOn())
						->setMostRecentEditBy($createdBy);
					$this->entityManager->persist($stream);
					$this->entityManager->flush($stream);
				}
			}, 200);

		$this->listeners[] = $events->getSharedManager()->attach(Application::class, StreamUpdated::class,
			function(Event $event) {
				$streamEvent = $event->getTarget();
				$id = $streamEvent->metadata()['aggregate_id'];
				if($streamEvent->metadata()['aggregate_type'] == KanbanizeStream::class){
					if(isset($streamEvent->payload()['subject'])) {
						$stream = $this->entityManager->find(Stream::class, $id);
						if(is_null($stream)) {
							return;
						}
						$updatedBy = $this->entityManager->find(User::class, $streamEvent->payload()['by']);
						$stream->setSubject($streamEvent->payload()['subject']);
						$stream->setMostRecentEditAt($streamEvent->occurredOn());
						$stream->setMostRecentEditBy($updatedBy);
						$this->entityManager->persist($stream);
						$this->entityManager->flush($stream);
					}
					if(isset($streamEvent->payload()['boardId'])) {
						$stream = $this->entityManager->find(Stream::class, $id);
						if(is_null($stream)) {
							return;
						}
						$updatedBy = $this->entityManager->find(User::class, $streamEvent->payload()['by']);
						$stream->setBoardId($streamEvent->payload()['boardId']);
						$stream->setMostRecentEditAt($streamEvent->occurredOn());
						$stream->setMostRecentEditBy($updatedBy);
						$this->entityManager->persist($stream);
						$this->entityManager->flush($stream);
					}
				}
			}, 200);
	}

	public function detach(EventManagerInterface $events){
		parent::detach($events);
		foreach ($this->listeners as $index => $listener) {
			if($events->getSharedManager()->detach(Application::class, $listeners[$index])) {
				unset($this->listeners[$index]);
			}
		}
	}

	protected function getPackage(){
		return 'Kanbanize';
	}
}
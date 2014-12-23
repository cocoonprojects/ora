<?php
namespace TaskManagement\Service;

use Doctrine\ORM\EntityManager;
use Prooph\EventStore\PersistenceEvent\PostCommitEvent;
use Prooph\EventStore\EventStore;
use Prooph\EventStore\Stream\StreamEvent;
use Ora\ReadModel\Task;

class TaskListener
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
			}
			
			$this->{$handler}($streamEvent);				
		}
 		$this->entityManager->flush();
	}
	
	protected function onTaskCreated(StreamEvent $event) {
		$id = $event->metadata()['aggregate_id'];
		$status = $event->payload()['status'];
		$createdBy = $this->entityManager->find('Ora\User\User', $event->payload()['by']);
		$entity = new Task($id);
		$entity->setStatus($status);
		$entity->setCreatedAt($event->occurredOn());
		$entity->setCreatedBy($createdBy);
		$entity->setMostRecentEditAt($event->occurredOn());
		$entity->setMostRecentEditBy($createdBy);
		$this->entityManager->persist($entity);
	}
	
	protected function onTaskUpdated(StreamEvent $event) {
		if(isset($event->payload()['subject'])) {
			$id = $event->metadata()['aggregate_id'];
			$entity = $this->entityManager->find('Ora\\ReadModel\\Task', $id);
			if(is_null($entity)) {
				return;
			}
			$entity->setSubject($event->payload()['subject']);
			$entity->setMostRecentEditAt($event->occurredOn());
			$updatedBy = $this->entityManager->find('Ora\User\User', $event->payload()['by']);
			$entity->setMostRecentEditBy($updatedBy);
			$this->entityManager->persist($entity);
		}
	}
	
	protected function onStreamChanged(StreamEvent $event) {
		$id = $event->metadata()['aggregate_id'];
		$entity = $this->entityManager->find('Ora\\ReadModel\\Task', $id);
		if(is_null($entity)) {
			return;
		}
		$streamId = $event->payload()['streamId'];
		$stream = $this->entityManager->find('Ora\ReadModel\Stream', $streamId);
		if(is_null($stream)) {
			return;
		}
		$entity->setStream($stream);
		$entity->setMostRecentEditAt($event->occurredOn());
		$updatedBy = $this->entityManager->find('Ora\User\User', $event->payload()['by']);
		$entity->setMostRecentEditBy($updatedBy);
		$this->entityManager->persist($entity);
	}

	protected function onMemberAdded(StreamEvent $event) {
		$id = $event->metadata()['aggregate_id'];
		$entity = $this->entityManager->find('Ora\\ReadModel\\Task', $id);
		if(is_null($entity)) {
			return;
        }

		$memberId = $event->payload()['userId'];
		$user = $this->entityManager->find('Ora\User\User', $memberId);
		if(is_null($user)) {
			return;
        }        
        $role = $event->payload()['role'];
		$entity->addMember($user, $role);
		$entity->setMostRecentEditAt($event->occurredOn());
		$addedBy = $this->entityManager->find('Ora\User\User', $event->payload()['by']);
		$entity->setMostRecentEditBy($addedBy);
		$this->entityManager->persist($entity);
		
	}
	
    protected function onMemberRemoved(StreamEvent $event) {

		$id = $event->metadata()['aggregate_id'];
		$entity = $this->entityManager->find('Ora\ReadModel\Task', $id);
		if(is_null($entity)) {
			return;
        }               

        $member = $this->entityManager->find('Ora\User\User', $event->payload()['userId']);
        $taskMember = $this->entityManager->getRepository('Ora\ReadModel\TaskMember')->findOneBy(array('member' => $member, 'task'=> $entity)); 
        
        $entity->removeMember($taskMember);
        $this->entityManager->remove($taskMember); 
        
		$entity->setMostRecentEditAt($event->occurredOn());
		$removedBy = $this->entityManager->find('Ora\User\User', $event->payload()['by']);
		$entity->setMostRecentEditBy($removedBy);
		$this->entityManager->persist($entity);
	}
	
	protected function onTaskDeleted(StreamEvent $event) {
		$id = $event->metadata()['aggregate_id'];
		$entity = $this->entityManager->find('Ora\\ReadModel\\Task', $id);
		if(is_null($entity)) {
			return;
		}
		$this->entityManager->remove($entity); // TODO: Solo con l'id no?
	}
	
	protected function determineEventHandlerMethodFor(StreamEvent $e)
    {
        return 'on' . join('', array_slice(explode('\\', $e->eventName()), -1));
    }
}

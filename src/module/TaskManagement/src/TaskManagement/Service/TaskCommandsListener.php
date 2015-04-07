<?php
namespace TaskManagement\Service;

use Doctrine\ORM\EntityManager;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Prooph\EventStore\Stream\StreamEvent;
use Ora\Service\SyncReadModelListener;
use Kanbanize\Entity\KanbanizeTask;
use Application\Entity\User;
use TaskManagement\Entity\Task;
use TaskManagement\Entity\Estimation;
use TaskManagement\Entity\Share;
use TaskManagement\Entity\Stream;
use TaskManagement\Entity\TaskMember;

class TaskCommandsListener extends SyncReadModelListener
{
	protected function onTaskCreated(StreamEvent $event) {
		$id = $event->metadata()['aggregate_id'];
		$status = $event->payload()['status'];
		$createdBy = $this->entityManager->find(User::class, $event->payload()['by']);
		
		switch($event->metadata()['aggregate_type']) {
			case 'Ora\\Kanbanize\\KanbanizeTask' :
				$entity = new KanbanizeTask($id);
				$entity->setBoardId($event->payload()['kanbanizeBoardId']);
				$entity->setTaskId($event->payload()['kanbanizeTaskId']);
				break;
			default:
				$entity = new Task($id);
		}
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
			$entity = $this->entityManager->find(Task::class, $id);
			if(is_null($entity)) {
				return;
			}
			$updatedBy = $this->entityManager->find(User::class, $event->payload()['by']);

			$entity->setSubject($event->payload()['subject']);
			$entity->setMostRecentEditAt($event->occurredOn());
			$entity->setMostRecentEditBy($updatedBy);
			$this->entityManager->persist($entity);
		}
	}
	
	protected function onTaskStreamChanged(StreamEvent $event) {
		$id = $event->metadata()['aggregate_id'];
		$entity = $this->entityManager->find(Task::class, $id);
		if(is_null($entity)) {
			return;
		}
		$streamId = $event->payload()['streamId'];
		$stream = $this->entityManager->find(Stream::class, $streamId);
		if(is_null($stream)) {
			return;
		}
		$updatedBy = $this->entityManager->find(User::class, $event->payload()['by']);

		$entity->setStream($stream);
		$entity->setMostRecentEditAt($event->occurredOn());
		$entity->setMostRecentEditBy($updatedBy);
		$this->entityManager->persist($entity);
	}

	protected function onTaskMemberAdded(StreamEvent $event) {
		$id = $event->metadata()['aggregate_id'];
		$entity = $this->entityManager->find(Task::class, $id);
		if(is_null($entity)) {
			return;
        }

		$memberId = $event->payload()['userId'];
		$user = $this->entityManager->find(User::class, $memberId);
		if(is_null($user)) {
			return;
        }        
        $role = $event->payload()['role'];
		$addedBy = $this->entityManager->find(User::class, $event->payload()['by']);
		$entity->addMember($user, $role, $addedBy, $event->occurredOn());
		$entity->setMostRecentEditAt($event->occurredOn());
		$entity->setMostRecentEditBy($addedBy);
		$this->entityManager->persist($entity);
	}
	
    protected function onTaskMemberRemoved(StreamEvent $event) {
		$id = $event->metadata()['aggregate_id'];
		$entity = $this->entityManager->find(Task::class, $id);
		if(is_null($entity)) {
			return;
        }               

        $member = $this->entityManager->find('Application\Entity\User', $event->payload()['userId']);
        $taskMember = $this->entityManager->getRepository(TaskMember::class)->findOneBy(array('member' => $member, 'task'=> $entity)); 
        
        $entity->removeMember($taskMember);
        $this->entityManager->remove($taskMember); 
        
		$entity->setMostRecentEditAt($event->occurredOn());
		$removedBy = $this->entityManager->find(User::class, $event->payload()['by']);
		$entity->setMostRecentEditBy($removedBy);
		$this->entityManager->persist($entity);
	}
	
	protected function onTaskDeleted(StreamEvent $event) {
		$id = $event->metadata()['aggregate_id'];
		$entity = $this->entityManager->find(Task::class, $id);
		if(is_null($entity)) {
			return;
		}
		$this->entityManager->remove($entity); // TODO: Solo con l'id no?
	}
	
	protected function onEstimationAdded(StreamEvent $event) {
		$memberId = $event->payload()['by'];
		$user = $this->entityManager->find(User::class, $memberId);
		if(is_null($user)) {
			return;
		}
		$id = $event->metadata()['aggregate_id'];
		$taskMember = $this->entityManager->find(TaskMember::class, array('task' => $id, 'member' => $user));
		
		$value = $event->payload()['value'];

		$taskMember->setEstimation(new Estimation($value, $event->occurredOn()));
		$this->entityManager->persist($taskMember);
	}
	
	protected function onTaskCompleted(StreamEvent $event) {
		$id = $event->metadata()['aggregate_id'];
		$task = $this->entityManager->find(Task::class, $id);
		$task->setStatus(Task::STATUS_COMPLETED);
		$task->resetShares();
        $user = $this->entityManager->find(User::class, $event->payload()['by']);
		$task->setMostRecentEditBy($user);
		$task->setMostRecentEditAt($event->occurredOn());
		$this->entityManager->persist($task);
	}
	
	protected function onTaskAccepted(StreamEvent $event) {
		$id = $event->metadata()['aggregate_id'];
		$task = $this->entityManager->find(Task::class, $id);
		$task->setStatus(Task::STATUS_ACCEPTED);
		$user = $this->entityManager->find(User::class, $event->payload()['by']);
		$task->setMostRecentEditBy($user);
		$task->setMostRecentEditAt($event->occurredOn());
		$this->entityManager->persist($task);
	}
	
	protected function onTaskClosed(StreamEvent $event) {
		$id = $event->metadata()['aggregate_id'];
		$task = $this->entityManager->find(Task::class, $id);
		$task->setStatus(Task::STATUS_CLOSED);
		$user = $this->entityManager->find(User::class, $event->payload()['by']);
		$task->setMostRecentEditBy($user);
		$task->setMostRecentEditAt($event->occurredOn());
		$this->entityManager->persist($task);
	}
	
	protected function onTaskOngoing(StreamEvent $event) {
		$id = $event->metadata()['aggregate_id'];
		$task = $this->entityManager->find(Task::class, $id);
		$task->setStatus(Task::STATUS_ONGOING);
		$user = $this->entityManager->find(User::class, $event->payload()['by']);
		$task->setMostRecentEditBy($user);
		$task->setMostRecentEditAt($event->occurredOn());
		$this->entityManager->persist($task);
	}
	
	protected function onSharesAssigned(StreamEvent $event) {
		$id = $event->metadata()['aggregate_id'];
		$task = $this->entityManager->find(Task::class, $id);
		$evaluator = $task->getMember($event->payload()['by']);
		$evaluator->resetShares();
		$shares = $event->payload()['shares'];
		foreach($shares as $key => $value) {
			$valued = $task->getMember($key);
			$evaluator->assignShare($valued, $value, $event->occurredOn());
		}
		$this->entityManager->persist($task);
	}
	
	protected function onSharesSkipped(StreamEvent $event) {
		$id = $event->metadata()['aggregate_id'];
		$task = $this->entityManager->find(Task::class, $id);
		$evaluator = $task->getMember($event->payload()['by']);
		$evaluator->resetShares();
		foreach($task->getMembers() as $valued) {
			$evaluator->assignShare($valued, null, $event->occurredOn());
		}
		$this->entityManager->persist($task);
	}

	protected function getPackage() {
		return 'TaskManagement';
	}
}

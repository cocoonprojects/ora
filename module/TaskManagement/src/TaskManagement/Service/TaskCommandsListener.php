<?php
namespace TaskManagement\Service;

use Application\Entity\User;
use Application\Service\ReadModelProjector;
use Prooph\EventStore\Stream\StreamEvent;
use TaskManagement\Task as WriteModelTask;
use TaskManagement\Entity\Estimation;
use TaskManagement\Entity\Stream;
use TaskManagement\Entity\Task;
use TaskManagement\Entity\TaskMember;
use TaskManagement\Entity\Vote;
use TaskManagement\Entity\ItemIdeaApproval;
use TaskManagement\Entity\ItemCompletedAcceptance;

class TaskCommandsListener extends ReadModelProjector
{
	protected function onTaskCreated(StreamEvent $event) {
		$id = $event->metadata()['aggregate_id'];
		$type = $event->metadata()['aggregate_type'];
		if($type == WriteModelTask::class){
			$stream = $this->entityManager->find(Stream::class, $event->payload()['streamId']);
			if(is_null($stream)) {
				return;
			}
			$createdBy = $this->entityManager->find(User::class, $event->payload()['by']);
			$entity = new Task($id, $stream);
			$entity->setStatus($event->payload()['status'])
				->setCreatedAt($event->occurredOn())
				->setCreatedBy($createdBy)
				->setMostRecentEditAt($event->occurredOn())
				->setMostRecentEditBy($createdBy);
			$this->entityManager->persist($entity);
		}
		return;
	}
	
	protected function onTaskUpdated(StreamEvent $event) {
		$id = $event->metadata()['aggregate_id'];
		$entity = $this->entityManager->find(Task::class, $id);
		if(is_null($entity)) {
			return;
		}
		$updatedBy = $this->entityManager->find(User::class, $event->payload()['by']);
		if(isset($event->payload()['subject'])) {
			$entity->setSubject($event->payload()['subject']);
		}
		if(isset($event->payload()['description'])) {
			$entity->setDescription($event->payload()['description']);
		}
		$entity->setMostRecentEditAt($event->occurredOn());
		$entity->setMostRecentEditBy($updatedBy);
		$this->entityManager->persist($entity);
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
		$user = $this->entityManager->find(User::class, $event->payload()['userId']);
		$addedBy = $this->entityManager->find(User::class, $event->payload()['by']);
		$role = $event->payload()['role'];
		$entity->addMember($user, $role, $addedBy, $event->occurredOn())
			->setMostRecentEditAt($event->occurredOn())
			->setMostRecentEditBy($addedBy);
		$this->entityManager->persist($entity);
	}
	
	protected function onTaskMemberRemoved(StreamEvent $event) {
		$id = $event->metadata()['aggregate_id'];
		$entity = $this->entityManager->find(Task::class, $id);
		$member = $this->entityManager->find(User::class, $event->payload()['userId']);
		$removedBy = $this->entityManager->find(User::class, $event->payload()['by']);
		$entity->removeMember($member)
			->setMostRecentEditAt($event->occurredOn())
			->setMostRecentEditBy($removedBy);
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
	
	protected function onTaskOpened(StreamEvent $event) {
		$id = $event->metadata()['aggregate_id'];
		$task = $this->entityManager->find(Task::class, $id);
		$task->setStatus(Task::STATUS_OPEN);
		$user = $this->entityManager->find(User::class, $event->payload()['by']);
		$task->setMostRecentEditBy($user);
		$task->setMostRecentEditAt($event->occurredOn());
		$this->entityManager->persist($task);
	}
	
	protected function onTaskArchived(StreamEvent $event) {
		$id = $event->metadata()['aggregate_id'];
		$task = $this->entityManager->find(Task::class, $id);
		$task->setStatus(Task::STATUS_ARCHIVED);
		$user = $this->entityManager->find(User::class, $event->payload()['by']);
		$task->setMostRecentEditBy($user);
		$task->setMostRecentEditAt($event->occurredOn());
		$this->entityManager->persist($task);
	}
	
	protected function onEstimationAdded(StreamEvent $event) {
		$memberId = $event->payload()['by'];
		$user = $this->entityManager->find(User::class, $memberId);
		if(is_null($user)) {
			return;
		}
		$id = $event->metadata()['aggregate_id'];
		$taskMember = $this->entityManager->find(TaskMember::class, ['task' => $id, 'user' => $user]);
		
		$value = $event->payload()['value'];

		$taskMember->setEstimation(new Estimation($value, $event->occurredOn()));
		$this->entityManager->persist($taskMember);
	}
	protected function onApprovalCreated(StreamEvent $event) {
		$memberId = $event->payload ()['by'];
		$description = $event->payload ()['description'];
		$user = $this->entityManager->find ( User::class, $memberId );
		if (is_null ( $user )) {
			return;
		}
		$id = $event->metadata ()['aggregate_id'];
		$taskId = $event->payload ()['task-id'];
		$task = $this->entityManager->find ( Task::class, $taskId );
		$vote = new Vote ( $event->occurredOn () );
		$vote->setValue ( $event->payload ()['vote'] );
		$task->addApproval ( $vote, $user, $event->occurredOn (), $description );
		$this->entityManager->persist ( $task );
	}

	protected function onAcceptanceCreated(StreamEvent $event) {
		$memberId = $event->payload()['by'];
		$description = $event->payload ()['description'];
		$user = $this->entityManager->find ( User::class, $memberId );
		if (is_null($user)) {
			return;
		}
		$id = $event->metadata ()['aggregate_id'];
		$taskId = $event->payload ()['task-id'];
		$task = $this->entityManager->find ( Task::class, $taskId );
		$vote = new Vote ( $event->occurredOn() );
		$vote->setValue ( $event->payload ()['vote'] );
		$task->addAcceptance( $vote, $user, $event->occurredOn (), $description );
		$this->entityManager->persist ( $task );
	}

	protected function onAcceptancesRemoved(StreamEvent $event) {
		$memberId = $event->payload()['by'];
		$user = $this->entityManager->find( User::class, $memberId );
		if (is_null( $user )) {
			return;
		}
		$taskId = $event->payload()['task-id'];
		$task = $this->entityManager->find( Task::class, $taskId );

		$task->removeAcceptances( $user );
		$this->entityManager->persist( $task );
	}
	
	protected function onTaskCompleted(StreamEvent $event) {
		$id = $event->metadata()['aggregate_id'];
		$task = $this->entityManager->find(Task::class, $id);
		$task->setStatus(Task::STATUS_COMPLETED);
		$task->resetShares();
		$user = $this->entityManager->find(User::class, $event->payload()['by']);
		$task->setMostRecentEditBy($user);
		$task->setMostRecentEditAt($event->occurredOn());
		$task->resetAcceptedAt();
		$this->entityManager->persist($task);
	}
	
	protected function onTaskAccepted(StreamEvent $event) {
		$id = $event->metadata()['aggregate_id'];
		$task = $this->entityManager->find(Task::class, $id);
		$task->setStatus(Task::STATUS_ACCEPTED);
		$user = $this->entityManager->find(User::class, $event->payload()['by']);
		$task->setMostRecentEditBy($user);
		$task->setMostRecentEditAt($event->occurredOn());
		$task->setAcceptedAt($event->occurredOn());
		if(isset($event->payload()['intervalForCloseTask'])){
			$sharesAssignmentExpiresAt = clone $event->occurredOn();
			$sharesAssignmentExpiresAt->add($event->payload()['intervalForCloseTask']);
			$task->setSharesAssignmentExpiresAt($sharesAssignmentExpiresAt);
		}
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

	protected function onCreditsAssigned(StreamEvent $event) {
		$id = $event->metadata()['aggregate_id'];
		$task = $this->entityManager->find(Task::class, $id);
		$credits = $event->payload()['credits'];
		foreach($task->getMembers() as $member) {
			$member->setCredits($credits[$member->getUser()->getId()]);
			$this->entityManager->persist($member);
		}
	}

	protected function onOwnerAdded(StreamEvent $event) {
		$id = $event->metadata()['aggregate_id'];
		$new_owner = $event->payload()['new_owner'];
		if(is_null($new_owner)){
			return;
		}
		$new_task_owner = $this->entityManager->find(TaskMember::class, ['task' => $id, 'user' => $new_owner]);
		$new_task_owner->setRole(TaskMember::ROLE_OWNER);
		$this->entityManager->persist($new_task_owner);
	}

	protected function onOwnerRemoved(StreamEvent $event) {
		$id = $event->metadata()['aggregate_id'];
		$ownerId = $event->payload()['ex_owner'];
		$ex_owner = $this->entityManager->find(User::class, $event->payload()['ex_owner']);
		$ex_task_owner = $this->entityManager->find(TaskMember::class, ['task' => $id, 'user' => $ex_owner]);
		if(is_null($ex_task_owner)){
			return;
		}
		$ex_task_owner->setRole(TaskMember::ROLE_MEMBER);
		$this->entityManager->persist($ex_task_owner);
	}

	protected function getPackage() {
		return 'TaskManagement';
	}
}

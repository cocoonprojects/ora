<?php

namespace Kanbanize\Service;

use Application\Entity\User;
use Application\Service\ReadModelProjector;
use Doctrine\ORM\EntityManager;
use Kanbanize\KanbanizeTask;
use Kanbanize\Entity\KanbanizeTask as ReadModelKanbanizeTask;
use People\Service\OrganizationService;
use Prooph\EventStore\Stream\StreamEvent;
use TaskManagement\Entity\Stream;
use TaskManagement\Service\TaskService;
use TaskManagement\TaskCreated;
use TaskManagement\TaskUpdated;
use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\Event;
use Zend\Mvc\Application;

class TaskCommandsListener extends ReadModelProjector{

	/**
	 * @var TaskService
	 */
	private $taskService;
	/**
	 * @var OrganizationService
	 */
	private $organizationService;
	/**
	 * @var NotificationService
	 */
	private $notificationService;

	public function __construct(EntityManager $entityManager, TaskService $taskService){
		parent::__construct($entityManager);
		$this->taskService = $taskService;
	}

	public function attach(EventManagerInterface $events){
		parent::attach($events);

		$this->listeners[] = $events->getSharedManager()->attach(Application::class, TaskCreated::class,
			function(Event $event) {
				$streamEvent = $event->getTarget();
				$id = $streamEvent->metadata()['aggregate_id'];
				if($streamEvent->metadata()['aggregate_type'] == KanbanizeTask::class){
					$stream = $this->entityManager->find(Stream::class, $streamEvent->payload()['streamId']);
					if(is_null($stream)) {
						return;
					}
					$createdBy = $this->entityManager->find(User::class, $streamEvent->payload()['by']);
					$entity = new ReadModelKanbanizeTask($id, $stream);
					$entity->setTaskId($streamEvent->payload()['taskid'])
						->setSubject($streamEvent->payload()['subject'])
						->setColumnName($streamEvent->payload()['columnname'])
						->setColumnName($streamEvent->payload()['lanename'])
						->setStatus($streamEvent->payload()['status'])
						->setCreatedAt($streamEvent->occurredOn())
						->setCreatedBy($createdBy)
						->setMostRecentEditAt($streamEvent->occurredOn())
						->setMostRecentEditBy($createdBy);
					$this->entityManager->persist($entity);
					$this->entityManager->flush($entity);
				}
		}, 200);

		$this->listeners[] = $events->getSharedManager()->attach(Application::class, TaskUpdated::class, 
			function(Event $event) {
				$streamEvent = $event->getTarget();
				if($streamEvent->metadata()['aggregate_type'] == KanbanizeTask::class){
					$id = $streamEvent->metadata()['aggregate_id'];
					$entity = $this->taskService->findTask($id);
					$by = $this->entityManager->find(User::class, $streamEvent->payload()['by']);
					$entity = $this->updateEntity($entity, $by, $streamEvent);
					$this->entityManager->persist($entity);
					$this->entityManager->flush($entity);
				}
		}, 200);
	}
	
	private function updateEntity(ReadModelKanbanizeTask $task, User $updatedBy, $streamEvent){

		if(isset($streamEvent->payload()['taskid'])) {
			$task->setTaskId($streamEvent->payload()['taskid']);
		}
		if(isset($streamEvent->payload()['columnname'])) {
			$task->setColumnName($streamEvent->payload()['columnname']);
		}
		if(isset($streamEvent->payload()['lanename'])) {
			$task->setLaneName($streamEvent->payload()['lanename']);
		}
		if(isset($streamEvent->payload()['assignee'])) {
			$task->setAssignee($streamEvent->payload()['assignee']);
		}
		if(isset($streamEvent->payload()['subject'])) {
			$task->setSubject($streamEvent->payload()['subject']);
		}
		$task->setMostRecentEditAt($streamEvent->occurredOn());
		$task->setMostRecentEditBy($updatedBy);
		return $task;
	}

	protected function getPackage() {
		return 'Kanbanize';
	}
	
	public function detach(EventManagerInterface $events){
		parent::detach($events);
		foreach ($this->listeners as $index => $listener) {
			if($events->getSharedManager()->detach(Application::class, $listeners[$index])) {
				unset($this->listeners[$index]);
			}
		}
	}
}
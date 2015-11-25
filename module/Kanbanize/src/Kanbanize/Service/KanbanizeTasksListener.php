<?php

namespace Kanbanize\Service;

use Application\Entity\User;
use Doctrine\ORM\EntityManager;
use Prooph\EventStore\Stream\StreamEvent;
use TaskManagement\Entity\Stream;
use TaskManagement\Entity\Task;
use TaskManagement\Service\TaskService;
use TaskManagement\TaskOngoing;
use TaskManagement\TaskUpdated;
use Zend\EventManager\Event;
use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\ListenerAggregateInterface;
use Zend\Mvc\Application;
use Kanbanize\Entity\KanbanizeTask as ReadModelKanbanizeTask;
use Kanbanize\KanbanizeTask;
use TaskManagement\TaskCreated;

class KanbanizeTasksListener implements ListenerAggregateInterface{
	
	/**
	 * @var TaskService
	 */
	private $taskService;

	private $listeners;
	

	public function __construct(TaskService $taskService, EntityManager $em){
		$this->taskService = $taskService;
		$this->entityManager = $em;
	}
	
	public function attach(EventManagerInterface $events){

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
			},200);
	}

	private function updateEntity(ReadModelKanbanizeTask $task, User $updatedBy, $streamEvent){

		if(isset($streamEvent->payload()['taskid'])) {
			$task->setTaskId($streamEvent->payload()['taskid']);
		}
		if(isset($streamEvent->payload()['columnname'])) {
			$task->setColumnName($streamEvent->payload()['columnname']);
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

	public function detach(EventManagerInterface $events)
	{
		foreach ($this->listeners as $index => $listener) {
			if($events->getSharedManager()->detach(Application::class, $listeners[$index])) {
				unset($this->listeners[$index]);
			}
		}
	}
}
<?php

namespace Kanbanize\Service;

use TaskManagement\Service\TaskService;
use TaskManagement\TaskAccepted;
use TaskManagement\TaskClosed;
use TaskManagement\TaskCompleted;
use TaskManagement\TaskOngoing;
use Zend\EventManager\ListenerAggregateInterface;
use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\Event;
use TaskManagement\Task;
use Kanbanize\KanbanizeTask;
use Zend\Mvc\Application;

class SyncTaskListener implements ListenerAggregateInterface
{
	/**
	 * @var KanbanizeService
	 */
	private $kanbanizeService;
	/**
	 * @var TaskService
	 */
	private $taskService;

	private $listeners;
	
	public function __construct(KanbanizeService $kanbanizeService, TaskService $taskService)
	{
		$this->kanbanizeService = $kanbanizeService;
		$this->taskService = $taskService;
	}

	public function attach(EventManagerInterface $events)
	{
		$this->listeners[] = $events->getSharedManager()->attach(Application::class, TaskOngoing::class, function(Event $event) {
			$streamEvent = $event->getTarget();
			$taskId = $streamEvent->metadata()['aggregate_id'];
			$task = $this->taskService->getTask($taskId);
			if($task instanceof KanbanizeTask) {
				$this->kanbanizeService->executeTask($task);
			}
		});
		$this->listeners[] = $events->getSharedManager()->attach(Application::class, TaskCompleted::class, function(Event $event) {
			$streamEvent = $event->getTarget();
			$taskId = $streamEvent->metadata()['aggregate_id'];
			$task = $this->taskService->getTask($taskId);
			if($task instanceof KanbanizeTask) {
				$this->kanbanizeService->completeTask($task);
			}
		});
		$this->listeners[] = $events->getSharedManager()->attach(Application::class, TaskAccepted::class, function(Event $event) {
			$streamEvent = $event->getTarget();
			$taskId = $streamEvent->metadata()['aggregate_id'];
			$task = $this->taskService->getTask($taskId);
			if($task instanceof KanbanizeTask) {
				$this->kanbanizeService->acceptTask($task);
			}
		});
		$this->listeners[] = $events->getSharedManager()->attach(Application::class, TaskClosed::class, function(Event $event) {
			$streamEvent = $event->getTarget();
			$taskId = $streamEvent->metadata()['aggregate_id'];
			$task = $this->taskService->getTask($taskId);
			if($task instanceof KanbanizeTask) {
				$this->kanbanizeService->closeTask($task);
			}
		});
	}
	
    public function detach(EventManagerInterface $events)
    {
    	foreach ($this->listeners as $index => $listener) {
			if($events->getSharedManager()->detach('TaskManagement\TaskService', $listeners[$index])) {
				unset($this->listeners[$index]);
			}
    	}
    }
}
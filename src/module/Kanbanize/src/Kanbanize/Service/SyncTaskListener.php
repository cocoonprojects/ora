<?php

namespace Kanbanize\Service;

use Zend\EventManager\ListenerAggregateInterface;
use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\Event;
use TaskManagement\Task;
use Kanbanize\KanbanizeTask;

class SyncTaskListener implements ListenerAggregateInterface
{
	/**
	 * 
	 * @var KanbanizeService
	 */
	private $kanbanizeService;

	private $listeners;
	
	public function __construct(KanbanizeService $kanbanizeService)
	{
		$this->kanbanizeService = $kanbanizeService;
	}
	
	public function attach(EventManagerInterface $events)
	{
		$that = $this;
		$this->listeners[] = $events->getSharedManager()->attach('TaskManagement\TaskService', Task::EVENT_ONGOING, function(Event $event) use ($that) {
			$task = $event->getTarget();
			if($task instanceof KanbanizeTask) {
				$that->kanbanizeService->executeTask($task);
			}
		});
		$this->listeners[] = $events->getSharedManager()->attach('TaskManagement\TaskService', Task::EVENT_COMPLETED, function(Event $event) use ($that) {
			$task = $event->getTarget();
			if($task instanceof KanbanizeTask) {
				$that->kanbanizeService->completeTask($task);
			}
		});
		$this->listeners[] = $events->getSharedManager()->attach('TaskManagement\TaskService', Task::EVENT_ACCEPTED, function(Event $event) use ($that) {
			$task = $event->getTarget();
			if($task instanceof KanbanizeTask) {
				$that->kanbanizeService->acceptTask($task);
			}
		});
		$this->listeners[] = $events->getSharedManager()->attach('TaskManagement\TaskService', Task::EVENT_CLOSED, function(Event $event) use ($that) {
			$task = $event->getTarget();
			if($task instanceof KanbanizeTask) {
				$that->kanbanizeService->closeTask($task);
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
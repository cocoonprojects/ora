<?php
namespace TaskManagement\Service;

use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\Event;
use Zend\EventManager\ListenerAggregateInterface;
use Ora\TaskManagement\Task;

class CloseTaskListener implements ListenerAggregateInterface {

	protected $listeners = array();
	
	public function attach(EventManagerInterface $events) {
		$this->listeners[] = $events->getSharedManager()->attach('Ora\TaskManagement\EventSourcingTaskService', Task::EVENT_SHARES_ASSIGNED, function(Event $event) {
			$task = $event->getTarget();
			$by = $event->getParam('by');
			if ($task->isSharesAssignmentCompleted()) {
				$task->close($by);
			}
		});
	}
	
    public function detach(EventManagerInterface $events)
    {
        foreach ($this->listeners as $index => $listener) {
            if ($events->detach($listener)) {
                unset($this->listeners[$index]);
            }
        }
    }
}
<?php
namespace TaskManagement\Service;

use Application\IllegalStateException;
use Application\InvalidArgumentException;
use Application\Service\UserService;
use Prooph\EventStore\EventStore;
use TaskManagement\SharesAssigned;
use TaskManagement\SharesSkipped;
use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\Event;
use Zend\EventManager\ListenerAggregateInterface;
use Zend\Mvc\Application;

class CloseTaskListener implements ListenerAggregateInterface {

	protected $listeners = array();
	/**
	 * @var TaskService
	 */
	protected $taskService;
	/**
	 * @var UserService
	 */
	protected $userService;
	/**
	 * @var EventStore
	 */
	private $transactionManager;

	public function __construct(TaskService $taskService, UserService $userService, EventStore $transactionManager) {
		$this->taskService = $taskService;
		$this->userService = $userService;
		$this->transactionManager = $transactionManager;
	}
	
	public function attach(EventManagerInterface $events) {
		$this->listeners[] = $events->getSharedManager()->attach(Application::class, SharesAssigned::class, array($this, 'processEvent'));
		$this->listeners[] = $events->getSharedManager()->attach(Application::class, SharesSkipped::class, array($this, 'processEvent'));
	}

	public function processEvent(Event $event) {
		$streamEvent = $event->getTarget();
		$taskId = $streamEvent->metadata()['aggregate_id'];
		$task = $this->taskService->getTask($taskId);
		$byId = $event->getParam('by');
		$by = $this->userService->findUser($byId);
		if ($task->isSharesAssignmentCompleted()) {
			$this->transactionManager->beginTransaction();
			try {
				$task->close($by);
				$this->transactionManager->commit();
			}catch( IllegalStateException $e ) {
				$this->transactionManager->rollback();
				throw $e;
			}catch( InvalidArgumentException $e ) {
				$this->transactionManager->rollback();
				throw $e;
			}
		}
	}
	
	public function detach(EventManagerInterface $events)
	{
		if($events->getSharedManager()->detach('TaskManagement\TaskService', $this->listeners[0])) {
			unset($this->listeners[0]);
		}
	}
}
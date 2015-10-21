<?php

namespace TaskManagement\Service;

use Application\Service\UserService;
use Prooph\EventStore\EventStore;
use TaskManagement\Task;
use TaskManagement\TaskClosed;
use Zend\EventManager\Event;
use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\ListenerAggregateInterface;
use Zend\Mvc\Application;

class AssignCreditsListener implements ListenerAggregateInterface{
	
	protected $listeners = array();
	/**
	 * @var TaskService
	 */
	private $taskService;
	/**
	 * @var EventStore
	 */
	private $transactionManager;
	/**
	 * @var UserService
	 */
	protected $userService;
	
	public function __construct(TaskService $taskService, UserService $userService, EventStore $transactionManager){
		$this->taskService = $taskService;
		$this->transactionManager = $transactionManager;
		$this->userService = $userService;
	}
	
	public function attach(EventManagerInterface $events) {
		$this->listeners[] = $events->getSharedManager()->attach(Application::class, TaskClosed::class,
			function(Event $event) {
				$streamEvent = $event->getTarget();
				$taskId = $streamEvent->metadata()['aggregate_id'];
				$task = $this->taskService->getTask($taskId);
				$byId = $event->getParam('by');
				$by = $this->userService->findUser($byId);
				$this->transactionManager->beginTransaction();
				try {
					$task->assignCredits($by);
					$this->transactionManager->commit();
				}catch( \Exception $e ) {
					$this->transactionManager->rollback();
					throw $e;
				}
			});
	}
	
	public function detach(EventManagerInterface $events){
		if($events->getSharedManager()->detach(Application::class, $this->listeners[0])) {
			unset($this->listeners[0]);
		}
	}
}
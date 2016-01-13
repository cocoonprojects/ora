<?php

namespace Kanbanize\Service;

use Application\Entity\User;
use Application\Service\UserService;
use Kanbanize\KanbanizeTask as Task;
use People\Organization;
use People\Service\OrganizationService;
use Rhumsaa\Uuid\Uuid;
use TaskManagement\Entity\TaskMember;
use TaskManagement\Service\TaskService;
use TaskManagement\Stream;
use ZFX\EventStore\Controller\Plugin\EventStoreTransactionPlugin;


class Importer{
	
	CONST API_URL_FORMAT = "https://%s.kanbanize.com/index.php/api/kanbanize";
	
	/**
	 * @var KanbanizeService
	 */
	private $kanbanizeService;
	/**
	 * @var TaskService
	 */
	private $taskService;
	/**
	 * @var EventStoreTransactionPlugin
	 */
	private $transactionManager;
	/**
	 * @var UserService
	 */
	private $userService;
	/**
	 * @var Organization
	 */
	private $organization;
	/**
	 * @var User
	 */
	private $requestedBy;
	private $api;
	private $errors = [];
	/**
	 * @var \DateInterval
	 */
	private $intervalForAssignShares;
	/**
	 * @var int
	 */
	private $createdTasks = 0,
			$updatedTasks = 0,
			$deletedTasks = 0;

	public function __construct(KanbanizeService $kanbanizeService,
			TaskService $taskService,
			EventStoreTransactionPlugin $transactionManager,
			UserService $userService,
			Organization $organization,
			User $requestedBy,
			KanbanizeAPI $api){
		$this->kanbanizeService = $kanbanizeService;
		$this->taskService = $taskService;
		$this->transactionManager = $transactionManager;
		$this->userService = $userService;
		$this->organization = $organization;
		$this->requestedBy = $requestedBy;
		$this->api = $api;
	}

	public function getErrors(){
		return $this->errors;
	}
	
	public function getImportResult(){
		return [
				"createdTasks" => $this->createdTasks,
				"updatedTasks" => $this->updatedTasks,
				"deletedTasks" => $this->deletedTasks,
				"errors" => $this->errors
		];
	}
	/**
	 * 
	 * @param string $boardId
	 * @param Stream $stream
	 */
	public function importTasks($boardId, Stream $stream) {
		try{
			$kanbanizeTasks = $this->api->getAllTasks($boardId);
			if(is_string($kanbanizeTasks)){
				$this->errors[] = "Cannot import tasks due to {$kanbanizeTasks}";
				return;
			}
			$tasksFound = [];
			foreach($kanbanizeTasks as $kanbanizeTask){
				try{
					$task = $this->importTask ( $boardId, $stream, $kanbanizeTask );
					$tasksFound[] = $task->getId();
				}catch (\Exception $e){
					$this->errors[] = "Cannot import task {taskId: {$kanbanizeTask['taskid']}, boardId: {$boardId} due to {$e->getMessage()}";
					$tasksFound[] = $kanbanizeTask['taskid'];
				}
			}
			$this->deleteTasks($boardId, $stream, $tasksFound);
		}catch(KanbanizeApiException $e){
			$this->errors[] = "Cannot import tasks due to {$e->getMessage()}";
		}}
	/**
	 * 
	 * @param string $boardId
	 * @param Stream $stream
	 * @param array $kanbanizeTask
	 * @throws \Exception
	 * @return Task
	 */
	public function importTask($boardId, Stream $stream, $kanbanizeTask) {
		$settings = $this->organization->getSettings(Organization::KANBANIZE_SETTINGS);
		if(!isset($settings['boards'][$boardId]['columnMapping'][$kanbanizeTask['columnname']])){
			throw new \Exception("Missing mapping for column {$kanbanizeTask['columnname']}");
		}
		$mapping = $settings['boards'][$boardId]['columnMapping'];
		$readModelTask = $this->kanbanizeService->findTask($kanbanizeTask['taskid'], $this->organization); //TODO: esplorare nuovi metadati per l'event store
		if(is_null($readModelTask)){
			$task = $this->createTask($kanbanizeTask, $stream);
			$status = $mapping[$kanbanizeTask['columnname']];
			if($status > Task::STATUS_IDEA){
				$this->transactionManager->begin();
				try {
					$this->updateTaskStatus($task, $kanbanizeTask['columnname'], $mapping);
					$this->transactionManager->commit();
				}
				catch (\Exception $e) {
					$this->transactionManager->rollback();
					throw $e;
				}
			}
		}else{
			$task = $this->taskService->getTask($readModelTask->getId());
		}
		return $this->updateTask($boardId, $task, $kanbanizeTask, $mapping);
	}

	private function updateTask($boardId, Task $task, $kanbanizeTask, $columnMapping){
		$this->transactionManager->begin();
		try {
			$this->updateTaskOwner($task, $kanbanizeTask['assignee']);
			if($task->getColumnName() != $kanbanizeTask['columnname']){
				$this->updateTaskStatus($task, $kanbanizeTask['columnname'], $columnMapping);
			}
			if($task->getSubject() != $kanbanizeTask['title']){
				$task->setSubject($kanbanizeTask['title'], $this->requestedBy);
			}
			$this->transactionManager->commit();
			$this->updatedTasks++;
		}catch (\Exception $e) {
			$this->transactionManager->rollback();
			$this->errors[] = "Cannot update task {subject: {$task->getSubject()}, taskId: {$kanbanizeTask['taskid']}, boardId: {$boardId}} due to {$e->getMessage()}";
		}
		return $task;
	}

	/**
	 * 
	 * @param array $kanbanizeTask
	 * @param Stream $stream
	 * @throws Exception
	 * @return \Kanbanize\KanbanizeTask
	 */
	private function createTask($kanbanizeTask, Stream $stream){
		$options = [
			"taskid" => $kanbanizeTask['taskid'],
			"columnname" => $kanbanizeTask['columnname']
		];
		$this->transactionManager->begin();
		try {
			$task = Task::create($stream, $kanbanizeTask['title'], $this->requestedBy, $options);
			$this->taskService->addTask($task);
			$task->setAssignee($kanbanizeTask['assignee'], $this->requestedBy);
			$this->transactionManager->commit();
			$this->createdTasks++;
		}
		catch (\Exception $e) {
			$this->transactionManager->rollback();
			throw $e;
		}
		return $task;
	}
	/**
	 * 
	 * @param Task $task
	 * @param string $columnName
	 * @param array $columnMapping
	 */
	private function updateTaskStatus(Task $task, $columnName, $columnMapping){
		switch ($columnMapping[$columnName]) {
			// case on destination column
			case Task::STATUS_IDEA:
			case Task::STATUS_OPEN:
				//TODO: to be implemented
				break;
			case Task::STATUS_ONGOING:
				switch ($task->getStatus()){
					case Task::STATUS_CLOSED:
						$task->accept($this->requestedBy);
					case Task::STATUS_ACCEPTED:
						$task->complete($this->requestedBy);
					case Task::STATUS_COMPLETED:
					case Task::STATUS_IDEA:
						$task->execute($this->requestedBy);
				}
				break;
			case Task::STATUS_COMPLETED:
				switch ($task->getStatus()){
					case Task::STATUS_CLOSED:
						$task->accept($this->requestedBy);
					case Task::STATUS_ACCEPTED:
					case Task::STATUS_ONGOING:
						$task->complete($this->requestedBy);
						break;
					case Task::STATUS_IDEA:
						$task->execute($this->requestedBy);
						$task->complete($this->requestedBy);
				}
				break;
			case Task::STATUS_ACCEPTED:
				switch ($task->getStatus()){
					case Task::STATUS_IDEA:
						$task->execute($this->requestedBy);
					case Task::STATUS_ONGOING:
						$task->complete($this->requestedBy);
					case Task::STATUS_COMPLETED:
					case Task::STATUS_CLOSED:
						$task->accept($this->requestedBy, $this->getIntervalForAssignShares());
				}
				break;
			case Task::STATUS_CLOSED:
				switch ($task->getStatus()){
					case Task::STATUS_IDEA:
						$task->execute($this->requestedBy);
					case Task::STATUS_ONGOING:
						$task->complete($this->requestedBy);
					case Task::STATUS_COMPLETED:
						$task->accept($this->requestedBy);
					case Task::STATUS_ACCEPTED:
						$task->close($this->requestedBy);
				}
				break;
		}
		$task->setColumnName($columnName, $this->requestedBy);
	}
	/**
	 * 
	 * @param Task $task
	 * @param string $username
	 */
	private function updateTaskOwner(Task $task, $username){
		$new_owner = $this->getNewTaskOwner($username);
		if (is_null($new_owner)){
			$task->removeOwner($this->requestedBy);
		}elseif (!is_null($task->getOwner())){
			if(!$task->hasMember($new_owner)){
				$task->addMember($new_owner, TaskMember::ROLE_MEMBER, $this->requestedBy);
			}
			$task->changeOwner($new_owner, $this->requestedBy);
		}elseif ($this->ownershipToBeAdded($task, $new_owner)){
			$task->addMember($new_owner, TaskMember::ROLE_OWNER, $this->requestedBy);
		}elseif($this->ownershipToBeAssigned($task, $new_owner)){
			$task->changeOwner($new_owner, $this->requestedBy);
		}
		$task->setAssignee($username, $this->requestedBy);
	}
	/**
	 * @param Task $task
	 * @param User $new_owner
	 * @return boolean
	 */
	private function ownershipToBeAdded(Task $task, User $new_owner){
		return is_null($task->getOwner()) && !$task->hasMember($new_owner);
	}
	/**
	 * @param Task $task
	 * @param User $new_owner
	 * @return boolean
	 */
	private function ownershipToBeAssigned(Task $task, User $new_owner){
		return is_null($task->getOwner()) && $task->hasMember($new_owner);
	}

	private function deleteTasks($boardId, Stream $stream, $tasksFound){
		$tasks = $this->taskService->findTasks($this->organization, null, null, ["streamId"=>$stream->getId()]);
		$tasksToDelete = array_filter($tasks, function($task) use ($tasksFound){
			return !in_array($task->getId(), $tasksFound);
		});
		array_walk($tasksToDelete, function($t) use($boardId){
			$task = $this->taskService->getTask($t->getId());
			$this->transactionManager->begin();
			try{
				$task->delete($this->requestedBy);
				$this->transactionManager->commit();
				$this->deletedTasks++;
			}catch (\Exception $e) {
				$this->transactionManager->rollback();
				$this->errors[] = "Cannot delete task {taskSubject: {$task->getSubject()}, boardId: {$boardId}} due to {$e->getMessage()}";
			}
		});
	}
	
	private function getNewTaskOwner($kanbanizeTaskAssignee){
		if($kanbanizeTaskAssignee != Task::EMPTY_ASSIGNEE){
			$users = $this->userService->findUsers(['kanbanizeusername' => $kanbanizeTaskAssignee]);
			return array_shift($users);
		}
		return null;
	}
	public function setIntervalForAssignShares(\DateInterval $interval){
		$this->intervalForAssignShares = $interval;
	}

	public function getIntervalForAssignShares(){
		return $this->intervalForAssignShares;
	}
}
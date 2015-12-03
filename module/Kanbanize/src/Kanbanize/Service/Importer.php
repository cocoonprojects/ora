<?php

namespace Kanbanize\Service;

use Application\Entity\User;
use Application\Service\UserService;
use Kanbanize\Entity\KanbanizeStream as ReadModelStream;
use Kanbanize\KanbanizeStream;
use TaskManagement\Stream;
use Kanbanize\KanbanizeTask as Task;
use People\Organization;
use Prooph\EventStore\EventStore;
use Rhumsaa\Uuid\Uuid;
use TaskManagement\Entity\TaskMember;
use TaskManagement\Service\StreamService;
use TaskManagement\Service\TaskService;
use People\Service\OrganizationService;


class Importer{
	
	/**
	 * @var KanbanizeService
	 */
	private $kanbanizeService;
	/**
	 * @var TaskService
	 */
	private $taskService;
	/**
	 * @var StreamService
	 */
	private $streamService;
	/**
	 * @var EventStore
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
	 * @var int
	 */
	private $createdStreams = 0,
			$updatedStreams = 0,
			$createdTasks = 0,
			$updatedTasks = 0,
			$deletedTasks = 0;

	public function __construct(KanbanizeService $kanbanizeService,
			TaskService $taskService,
			StreamService $streamService,
			EventStore $transactionManager,
			UserService $userService,
			Organization $organization,
			User $requestedBy,
			KanbanizeAPI $api){
		$this->kanbanizeService = $kanbanizeService;
		$this->taskService = $taskService;
		$this->streamService = $streamService;
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
				"createdStreams" => $this->createdStreams,
				"updatedStreams" => $this->updatedStreams,
				"createdTasks" => $this->createdTasks,
				"updatedTasks" => $this->updatedTasks,
				"deletedTasks" => $this->deletedTasks,
				"errors" => $this->errors
		];
	}
	
	public function importProjects(){
		try{
			$projects = $this->api->getProjectsAndBoards();
			foreach ($projects as $project){
				$this->importProject($project);
			}
		}
		catch(KanbanizeApiException $e){
			$this->errors[] = "Cannot import projects due to {$e->getMessage()}";
		}
	}
	/**
	 * 
	 * @param array $project must contain name, id, boards
	 * 
	 */
	public function importProject($project){
		foreach ($project['boards'] as $board){
			try{
				$this->importBoard($project, $board);
			}catch (\Exception $e){
				$this->errors[] = "Cannot import board {boardId: {$board['id']}, projectId: {$project['id']}} due to {$e->getMessage()}";
			}
		}
	}
	/**
	 * 
	 * @param array $project
	 * @param array $board
	 */
	public function importBoard($project, $board){
		$tasksFound = [];
		$discardedTasks = [];
		$discardedStreams = [];
		$s = $this->kanbanizeService->findStreamByBoardId($board['id']);
		if(is_null($s)){
			$stream = $this->createStream ( $project, $board );
		}else{
			try{
				$stream = $this->updateStream ( $project, $board, $s);
			}catch (\Exception $e){
				$this->errors[] = "Cannot update stream for board {boardId: {$board['id']}, projectId: {$project['id']}} due to {$e->getMessage()}";
			}
		}
		$this->importTasks ( $project, $board, $stream);
	}
	/**
	 * 
	 * @param array $project
	 * @param array $board
	 * @param ReadModelStream $s
	 * @throws Exception
	 * @return Stream
	 */
	private function updateStream($project, $board, ReadModelStream $s) {
		$subject = $this->createFormattedSubject($project['name'], $board['name']);
		$stream = $this->streamService->getStream($s->getId());
		if($s->getSubject() != $subject ){
			$this->transactionManager->beginTransaction();
			try{
				$stream->setSubject($subject, $this->requestedBy);
				$this->transactionManager->commit();
				$this->updatedStreams++;
			}catch (\Exception $e) {
				$this->transactionManager->rollback();
				throw $e;
			}
		}
		return $stream;
	}
	
	/**
	 * 
	 * @param array $project
	 * @param array $board
	 * @throws Exception
	 * @return Stream
	 */
	private function createStream($project, $board) {
		$subject = $this->createFormattedSubject($project['name'], $board['name']);
		$options = [
			'boardId' => $board['id'],
			'projectId' => $project['id']
		];
	
		$this->transactionManager->beginTransaction();
		try {
			$stream = KanbanizeStream::create($this->organization, $subject, $this->requestedBy, $options);
			$this->streamService->addStream($stream);
			$this->transactionManager->commit();
			$this->createdStreams++;
			return $stream;
		}catch (\Exception $e) {
			$this->transactionManager->rollback();
			throw $e;
		}
	}
	/**
	 * @param tasksFound
	 * @param ex
	 * @param kanbanizeTasks
	 * @param readModelTask
	 * @param taskId
	 * @param task
	 * @param createdTaskId
	 */
	public function importTasks($project, $board, Stream $stream) {
		try{
			$kanbanizeTasks = $this->api->getAllTasks($board['id']);
			$tasksFound = [];
			foreach($kanbanizeTasks as $kanbanizeTask){
				try{
					$task = $this->importTask ( $project, $board, $stream, $kanbanizeTask );
					$tasksFound[] = $task->getId();
				}catch (\Exception $e){
					$this->errors[] = "Cannot import task {taskId: {$kanbanizeTask['taskid']}, boardId: {$board['id']}, projectId: {$project['id']}} due to {$e->getMessage()}";
					$tasksFound[] = $kanbanizeTask['taskid'];
				}
			}
			$this->deleteTasks($stream, $tasksFound);
		}catch(KanbanizeApiException $e){
			$this->errors[] = "Cannot import tasks due to {$e->getMessage()}";
		}}
	/**
	 * 
	 * @param string $project
	 * @param string $board
	 * @param Stream $stream
	 * @param array $kanbanizeTask
	 * @return \Kanbanize\KanbanizeTask|Task
	 */
	public function importTask($project, $board, Stream $stream, $kanbanizeTask) {
		$readModelTask = $this->kanbanizeService->findByTaskId($kanbanizeTask['taskid']); //TODO: esplorare nuovi metadati per l'event store
		if(is_null($readModelTask)){
			return $this->createTask($kanbanizeTask, $stream);
		}
		$task = $this->taskService->getTask($readModelTask->getId());
		return $this->updateTask($project, $board, $task, $kanbanizeTask);
	}

	private function updateTask($project, $board, Task $task, $kanbanizeTask){
		$this->transactionManager->beginTransaction();
		try {
			$this->updateTaskOwner($task, $kanbanizeTask['assignee']);
			if($task->getColumnName() != $kanbanizeTask['columnname']){
				$this->updateTaskStatus($task, $kanbanizeTask['columnname']);
			}
			if($task->getSubject() != $kanbanizeTask['title']){
				$task->setSubject($kanbanizeTask['title'], $this->requestedBy);
			}
			$this->transactionManager->commit();
			$this->updatedTasks++;
		}catch (\Exception $e) {
			$this->transactionManager->rollback();
			$this->errors[] = "Cannot update task {subject: {$task->getSubject()}, taskId: {$kanbanizeTask['taskid']}, boardId: {$board['id']}, projectId: {$project['id']}} due to {$e->getMessage()}";
		}
		return $task;
	}

	/**
	 * 
	 * @param array $kanbanizeTask
	 * @param Stream $stream
	 * @throws Exception
	 * @return Task
	 */
	private function createTask($kanbanizeTask, Stream $stream){
		$mapping = $this->organization->getSetting('kanbanizeColumnMapping');
		$status = $mapping[strtoupper($kanbanizeTask['columnname'])];
		$options = [
			"taskid" => $kanbanizeTask['taskid'],
			"columnname" => $kanbanizeTask['columnname'],
			"status" => $status
		];
		$new_owner = $this->getNewTaskOwner($kanbanizeTask['assignee']);
		$this->transactionManager->beginTransaction();
		try {
			$task = Task::create($stream, $kanbanizeTask['title'], $this->requestedBy, $options);
			$this->taskService->addTask($task);
			if(!is_null($new_owner)){
				$task->addMember($new_owner, TaskMember::ROLE_OWNER, $this->requestedBy);
			}
			$task->setAssignee($kanbanizeTask['assignee'], $this->requestedBy);
			$this->transactionManager->commit();
			$this->createdTasks++;
			return $task;
		}
		catch (\Exception $e) {
			$this->transactionManager->rollback();
			throw $e;
		}
	}
	/**
	 *
	 * @param Task $task
	 * @param unknown $columnName
	 * @param User $requestedBy
	 * @param KanbanizeImporterErrorsBuilder $errorsBuilder
	 */
	private function updateTaskStatus(Task $task, $columnName){
		$mapping = $this->organization->getSetting('kanbanizeColumnMapping');
		switch ($mapping[strtoupper($columnName)]) {
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
						$task->accept($this->requestedBy);
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
		$users = $this->userService->findUsers(['kanbanizeusername' => $username]);
		$new_owner = is_null($users) ? null : array_shift($users);
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
	private function createFormattedSubject($projectName, $boardName){
		return $projectName."/".$boardName;
	}

	private function deleteTasks(Stream $stream, $tasksFound){
		$tasks = $this->taskService->findTasks($this->organization, null, null, ["streamId"=>$stream->getId()]);
		$tasksToDelete = array_filter($tasks, function($task) use ($tasksFound){
			return !in_array($task->getId(), $tasksFound);
		});
		array_walk($tasksToDelete, function($t) {
			$task = $this->taskService->getTask($t->getId());
			$this->transactionManager->beginTransaction();
			try{
				$task->delete($this->requestedBy);
				$this->transactionManager->commit();
				$this->deletedTasks++;
			}catch (\Exception $e) {
				$this->transactionManager->rollback();
				$this->errors[] = "Cannot delete task {taskSubject: {$task->getSubject()}, boardId: {$board['id']}, projectId: {$project['id']}} due to {$e->getMessage()}";
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
	
}
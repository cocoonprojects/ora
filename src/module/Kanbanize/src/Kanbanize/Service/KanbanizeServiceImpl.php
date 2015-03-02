<?php

namespace Kanbanize\Service;

use Kanbanize\Entity\KanbanizeTask;

/**
 * Service Kanbanize
 *
 * @author Andrea Lupia <alupia@dimes.unical.it>
 *
 */
class KanbanizeServiceImpl implements KanbanizeService 
{
	/**
	 * Kanbanize API
	 *
	 * @var KanbanizeAPI
	 */
	private $kanbanize;
	
	/*
	 * Constructs service
	 */
	public function __construct(KanbanizeAPI $api) {
		$this->kanbanize = $api;
	}

	private function moveTask(KTask $kanbanizeTask, $status) {
  		$boardId = $kanbanizeTask->getBoardId();
  		$taskId = $kanbanizeTask->getTaskId();
  		$response = $this->kanbanize->moveTask($boardId, $taskId, $status);
  		if($response != 1) {
  			throw new OperationFailedException('Unable to move the task ' + $taskId + ' in board ' + $boardId + 'to the column ' + $status + ' because of ' + $response);
  		}
  		
  		return 1;
	}

	/**
	 *
	 * @param        	
	 *
	 */
	public function createNewTask($projectId, $taskSubject, $boardId) {
		$createdAt = new \DateTime ();
		
		// TODO: Modificare createdBy per inserire User
		$createdBy = "NOME UTENTE INVENTATO";
		
		$options = array (
				'description' => $taskSubject 
		);
		$id = $this->kanbanize->createNewTask ( $boardId, $options );
		if (is_null ( $id )) {
			throw OperationFailedException("Cannot create task on Kanbanize");
		} else {
			$kanbanizeTask = new KTask ( uniqid (), $boardId, $id, $createdAt, $createdBy );
			return 1;
		}
	}
	
	public function deleteTask(KTask $kanbanizeTask) {
		$boardId = $kanbanizeTask->getBoardId();
		$taskId = $kanbanizeTask->getTaskId();
		$ans = $this->kanbanize->deleteTask($boardId, $taskId);
		//return isset($ans['Error']) ? $ans['Error'] : $ans;
		if (isset($ans["Error"]))
			throw new OperationFailedException($ans["Error"]);
		
		
		return $ans;
	}
	
	/**
	 *
	 * @param int		$boardId
	 * @param string	$status
	 * 
	 *
	 */
	public function getTasks($boardId, $status = null) {
		$tasks_to_return = array ();
		$tasks = $this->kanbanize->getAllTasks ( $boardId );
		if (is_null ( $status ))
			return $tasks;
		else {
			foreach ( $tasks as $singletask ) {
				if ($singletask ["columnname"] == $status)
					$tasks_to_return [] = $singletask;
			}
			
			return $tasks_to_return;
		}
	}

	public function acceptTask(KTask $task) {
		$info = $this->kanbanize->getTaskDetails($task->getBoardId(), $task->getTaskId());
  		if(isset($info['Error'])) {
  			throw new OperationFailedException($info["Error"]);
  		}
		if ( $info['columnname'] == KTask::COLUMN_ACCEPTED){
			return;
		}
		if($info['columnname'] == KTask::COLUMN_COMPLETED){
			$this::moveTask($task, KTask::COLUMN_ACCEPTED);
		}else{
			throw new IllegalRemoteStateException("Cannot accpet a task which is " + $info["columnname"]);
		}
	}

	public function executeTask(KTask $task) {
		$info = $this->kanbanize->getTaskDetails($task->getBoardId(), $task->getTaskId());
  		if(isset($info['Error'])) {
  			throw new OperationFailedException($info["Error"]);
  		}
  		if($info["columnname"] == KTask::COLUMN_ONGOING){
  			return;
  		}
  		
		if($info['columnname'] == KTask::COLUMN_COMPLETED || $info['columnname'] == KTask::COLUMN_OPEN){
			$this::moveTask($task, KTask::COLUMN_ONGOING);
		}else{
			throw new IllegalRemoteStateException("Cannot move task in ongoing from "+$info["columnname"]);
		}
	}
	
	public function completeTask(KTask $task) {
		$info = $this->kanbanize->getTaskDetails($task->getBoardId(), $task->getTaskId());
		if(isset($info['Error'])) {
			throw new OperationFailedException($info["Error"]);
		}
		if($info["columnname"] == KTask::COLUMN_COMPLETED){
			return;
		}
		if (in_array($info['columnname'], [KTask::COLUMN_ONGOING, KTask::COLUMN_ACCEPTED])) {
			$this->moveTask($task, KTask::COLUMN_COMPLETED);
		} else {
			throw new IllegalRemoteStateException("Cannot move task in completed from "+$info["columnname"]);
		}
	}
	
	public function closeTask(KTask $task) {
		// TODO: To be implemented
	}
}
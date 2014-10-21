<?php

namespace Ora\TaskManagement;

/**
 * @author Giannotti Fabio
 */
interface TaskService
{
	public function createNewTask($project, $taskSubject);
	
	public function editTask($task, $data);
	
	public function findTaskByID($id);
	
	public function listAvailableTasks();
}
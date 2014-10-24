<?php

namespace Ora\TaskManagement;

/**
 * @author Giannotti Fabio
 */
interface TaskService
{
	public function createNewTask(\Ora\ProjectManagement\Project $project, $taskSubject);
	
	public function editTask(\Ora\TaskManagement\Task $task);
	
	public function findTask($id);
	
	public function deleteTask(\Ora\TaskManagement\Task $task);
	
	public function listAvailableTasks();
	
	public function addTaskMember(\Ora\TaskManagement\Task $task, \Ora\User\User $user);
	
	public function removeTaskMember(\Ora\TaskManagement\Task $task, \Ora\User\User $user);
}
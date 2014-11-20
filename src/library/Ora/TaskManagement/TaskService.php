<?php

namespace Ora\TaskManagement;

use Ora\ProjectManagement\Project;
use Ora\User\User;
use Ora\ReadModel\Project as ReadModelProject;

/**
 * @author Giannotti Fabio
 */
interface TaskService
{
	public function createTask(Project $project, $subject, User $createdBy);
	
	public function editTask(Task $task);
	
	public function getTask($id);
	
	public function deleteTask(Task $task, User $deletedBy);
	
	public function listAvailableTasks();
	
	public function findTaskById($id);
	
	public function findProjectTasks(ReadModelProject $project);
}
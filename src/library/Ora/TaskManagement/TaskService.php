<?php

namespace Ora\TaskManagement;

use Ora\StreamManagement\Stream;
use Ora\User\User;

/**
 * TODO: Rename in TaskRepository?
 */
interface TaskService
{
	public function addTask(Task $task);
	
	public function getTask($id);
	
	public function findTasks();
	
	public function findTask($id);
	
	public function findStreamTasks($streamId);
}
<?php

namespace Ora\TaskManagement;

use Ora\StreamManagement\Stream;
use Ora\User\User;
use Ora\ReadModel\Stream as ReadModelStream;

/**
 * @author Giannotti Fabio
 */
interface TaskService
{
	public function createTask(Stream $stream, $subject, User $createdBy);
	
	public function editTask(Task $task);
	
	public function getTask($id);
	
	public function deleteTask(Task $task, User $deletedBy);
	
	public function findTasks();
	
	public function findTask($id);
	
	public function findStreamTasks(ReadModelStream $stream);
}
<?php

namespace Ora\TaskManagement;

use Ora\StreamManagement\Stream;
use Ora\User\User;

/**
 * @author Giannotti Fabio
 */
interface TaskService
{
	public function createTask(Stream $stream, $subject, User $createdBy);
	
	public function getTask($id);
	
	public function findTasks();
	
	public function findTask($id);
	
	public function findStreamTasks($streamId);
}
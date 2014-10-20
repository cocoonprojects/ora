<?php

namespace Ora\TaskManagement;

/**
 * @author Giannotti Fabio
 */
interface TaskService
{
	public function createNewTask($project, $taskSubject);
	
	public function listAvailableTasks();
}
<?php

namespace Ora\TaskManagement;

interface TaskService
{
	public function createNewTask($projectID, $taskSubject);
	
	public function listAvailableTasks();
}
<?php

namespace Ora\TaskManagement;

interface TaskService
{
	public function createNewTask($projectID, $taskSubject);
}
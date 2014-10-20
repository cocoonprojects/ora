<?php

namespace Ora\Kanbanize;

interface KanbanizeService {

	CONST COLUMN_IDEA = 'Idea';
	CONST COLUMN_OPEN = 'Open';
	CONST COLUMN_ONGOING = "OnGoing";
	CONST COLUMN_COMPLETED = 'Completed';
	CONST COLUMN_ACCEPTED = 'Accepted';
	
	public function acceptTask($kanbanizeTask);
	
	public function createNewTask($projectId, $taskSubject, $boardId);
	
	public function getTasks($boardId, $status);
}

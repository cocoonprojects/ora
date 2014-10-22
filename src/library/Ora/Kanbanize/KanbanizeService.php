<?php

namespace Ora\Kanbanize;

interface KanbanizeService {

	public function moveTask(KanbanizeTask $kanbanizeTask, $status);
	
	public function createNewTask($projectId, $taskSubject, $boardId);
	
	public function getTasks($boardId, $status = null);
	
	public function isAcceptable(KanbanizeTask $kanbanizeTask);
}

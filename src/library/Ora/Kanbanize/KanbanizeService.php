<?php

namespace Ora\Kanbanize;

interface KanbanizeService {

	//public function moveTask(KanbanizeTask $kanbanizeTask, $status);
	
	public function createNewTask($projectId, $taskSubject, $boardId);
	
	public function deleteTask(KanbanizeTask $kanbanizeTask);
	
	public function getTasks($boardId, $status = null);
	
	public function acceptTask(KanbanizeTask $kanbanizeTask);
	
	public function moveBackToOngoing(KanbanizeTask $kanbanizeTask);
}

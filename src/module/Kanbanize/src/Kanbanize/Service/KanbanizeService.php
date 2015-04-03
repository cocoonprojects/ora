<?php

namespace Kanbanize\Service;

use Ora\Kanbanize\ReadModel\KanbanizeTask;

interface KanbanizeService {

	//public function moveTask(KanbanizeTask $kanbanizeTask, $status);
	
	public function createNewTask($projectId, $taskSubject, $boardId);
	
	public function deleteTask(KanbanizeTask $kanbanizeTask);
	
	public function getTasks($boardId, $status = null);
	
	public function acceptTask(KanbanizeTask $task);
	
	public function executeTask(KanbanizeTask $kanbanizeTask);
		
	public function completeTask(KanbanizeTask $task);
	
	public function closeTask(KanbanizeTask $task);
}

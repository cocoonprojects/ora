<?php

namespace Kanbanize\Service;

use Kanbanize\Entity\KanbanizeTask;

interface KanbanizeService {

	//public function moveTask(KanbanizeTask $kanbanizeTask, $status);
	
	public function createNewTask($projectId, $taskSubject, $boardId);
	
	public function deleteTask(KTask $kanbanizeTask);
	
	public function getTasks($boardId, $status = null);
	
	public function acceptTask(KTask $task);
	
	public function executeTask(KTask $kanbanizeTask);
		
	public function completeTask(KTask $task);
	
	public function closeTask(KTask $task);
}

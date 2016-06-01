<?php

namespace Kanbanize\Service;

use Kanbanize\KanbanizeTask;
use Kanbanize;

interface KanbanizeService {

	public function moveTask(KanbanizeTask $kanbanizeTask, $status);
	
	public function moveTaskonKanbanize(Kanbanize\Entity\KanbanizeTask $kanbanizeTask, $status,$boardId);
	
	public function createNewTask($projectId, $taskSubject, $taskTitle, $boardId);
	
	public function deleteTask(KanbanizeTask $kanbanizeTask);
	
	public function getTasks($boardId, $status = null);
	
	public function acceptTask(KanbanizeTask $task);
	
	public function executeTask(KanbanizeTask $kanbanizeTask);
		
	public function completeTask(KanbanizeTask $task);
	
	public function closeTask(KanbanizeTask $task);

	public function findStreamByBoardId($boardId, $organization);

	public function findTask($taskId, $organization);
	
	public function initApi($apiKey, $subdomain);

}

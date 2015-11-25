<?php

namespace Kanbanize\Service;

use Kanbanize\KanbanizeTask;

interface KanbanizeService {

	//public function moveTask(KanbanizeTask $kanbanizeTask, $status);
	
	/* Actions on kanbanize tasks come directly from Kanbanize, not from O.R.A.
	 * 
	public function createNewTask($projectId, $taskSubject, $boardId);
	
	public function deleteTask(KanbanizeTask $kanbanizeTask);
	
	public function getTasks($boardId, $status = null);
	
	public function acceptTask(KanbanizeTask $task);
	
	public function executeTask(KanbanizeTask $kanbanizeTask);
		
	public function completeTask(KanbanizeTask $task);
	
	public function closeTask(KanbanizeTask $task);
	*/
	public function findByBoardId($boardId);

	public function findByTaskId($taskId);

}

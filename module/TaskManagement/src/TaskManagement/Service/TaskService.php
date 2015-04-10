<?php

namespace TaskManagement\Service;

use TaskManagement\Stream;
use TaskManagement\Task;
use Application\Entity\User;
use Zend\View\Renderer\RendererInterface;


/**
 * TODO: Rename in TaskRepository?
 */
interface TaskService
{
	/**
	 * 
	 * @param Task $task
	 * @return Task
	 */
	public function addTask(Task $task);
	/**
	 * 
	 * @param string|Uuid $id
	 * @return Task|null
	 */
	public function getTask($id);
	
	public function findTasks();
	
	public function findTask($id);
	
	public function findStreamTasks($streamId);
	
	public function getAcceptedTaskIdsToNotify(\DateInterval $interval);
	
	public function getAcceptedTaskIdsToClose(\DateInterval $interval);

	public function notifyMembersForShareAssignment(Task $task, RendererInterface $renderer, $taskMembersWithEmptyShares);
	
	public function findMembersWithEmptyShares(Task $task);

}
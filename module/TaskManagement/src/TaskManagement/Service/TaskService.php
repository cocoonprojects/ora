<?php

namespace TaskManagement\Service;

use TaskManagement\Stream;
use TaskManagement\Task;
use Application\Entity\User;
<<<<<<< HEAD
use Zend\View\Renderer\RendererInterface;
=======
use Zend\View\Renderer\RendererInterface;
>>>>>>> completed configuration for calling action from localhost

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
<<<<<<< HEAD
	
	public function notifyMembersForShareAssignment(Task $task, RendererInterface $renderer, $taskMembersWithEmptyShares);
	
	public function findMembersWithEmptyShares(Task $task);

=======
	
	public function notifyMembersForShareAssignment(Task $task, RendererInterface $renderer, $taskMembersWithEmptyShares);
	
	public function findMembersWithEmptyShares(Task $task);
>>>>>>> completed configuration for calling action from localhost
}
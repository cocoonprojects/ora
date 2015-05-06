<?php

namespace TaskManagement\Service;

use TaskManagement\Stream;
use TaskManagement\Task;
use TaskManagement\Entity\Task as ReadModelTask;
use Zend\View\Renderer\PhpRenderer;


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

	/**
	 * Find accepted tasks with accepted date before $interval days from now
	 * @param \DateInterval $interval
	 * @return array
	 */
	public function findAcceptedTasksBefore(\DateInterval $interval);

	/**
	 * Retrieve an array of members (Application\Entity\User) of $task that haven't assigned any share
	 *
	 * @param TaskManagement\Entity\Task $task
	 * @return array of Application\Entity\User or empty array
	 */
	public function findMembersWithEmptyShares(ReadModelTask $task);
}
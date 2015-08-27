<?php

namespace TaskManagement\Service;

use People\Entity\Organization;
use TaskManagement\Task;


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
	
	/**
	 * Get the list of all available tasks
	 *
	 * @param Organization $organization
	 * @return Task[]
	 */
	public function findTasks(Organization $organization);

	/**
	 * @param string|Uuid $id
	 * @return Task|null
	 */
	public function findTask($id);

	/**
	 * @param string|Uuid $streamId
	 * @return Task[]
	 */
	public function findStreamTasks($streamId);

	/**
	 * Find accepted tasks with accepted date before $interval days from now
	 * @param \DateInterval $interval
	 * @return array
	 */
	public function findAcceptedTasksBefore(\DateInterval $interval);

}

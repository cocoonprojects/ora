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
	 * Get the list of all available tasks in the $offset - $limit interval
	 *
	 * @param Organization $organization
	 * @param integer $offset
	 * @param integer $limit
	 * @param array $filters
	 * @return Task[]
	 */
	public function findTasks(Organization $organization, $offset, $limit, $filters);

	/**
	 * @param string|Uuid $id
	 * @return Task|null
	 */
	public function findTask($id);

	/**
	 * Find accepted tasks with accepted date before $interval days from now
	 * @param \DateInterval $interval
	 * @return array
	 */
	public function findAcceptedTasksBefore(\DateInterval $interval);

	/**
	 * Get the number of tasks of an $organization
	 * @param Organization $organization
	 * @param \DateTime $filters["startOn"]
	 * @param \DateTime $filters["endOn"]
	 * @param String $filters["memberId"]
	 * @param String $filters["memberEmail"]
	 * @return integer
	 */
	public function countOrganizationTasks(Organization $organization, $filters);

	/**
	 * Get tasks statistics for $memberId 
	 * @param Organization $org
	 * @param string $memberId
	 * @param \DateTime $filters["startOn"]
	 * @param \DateTime $filters["endOn"]
	 */
	public function findMemberStats(Organization $org, $memberId, $filters);
}

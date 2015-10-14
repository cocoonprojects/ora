<?php

namespace TaskManagement\Service;

use Application\Entity\User;
use TaskManagement\Entity\Task;
use People\Entity\Organization;
use TaskManagement\Stream;
use People\Entity\OrganizationMembership;

interface NotificationService
{
	/**
	 * Send notification to owner
	 * @param Task $task
	 * @param User $member
	 * @return bool
	 */
	public function sendEstimationAddedInfoMail(Task $task, User $member);

	/**
	 * Send notification to owner
	 * @param Task $task
	 * @param User $member
	 * @return bool
	 */
	public function sendSharesAssignedInfoMail(Task $task, User $member);

	/**
	 * Send notification to members that haven't assigned shares yet
	 * @param Task $task
	 * @return void
	 */
	public function remindAssignmentOfShares(Task $task);

	/**
	 * Send notification to members that haven't estimate yet
	 * @param Task $task
	 * @return void
	 */
	public function remindEstimation(Task $task);

	/**
	 * Send notification to members
	 * @param Task $task
	 * @return void
	 */
	public function sendTaskClosedInfoMail(Task $task);
	
	/**
	 * Send notification to members that a new Work Item Idea has been created
	 * @param Task $task
	 * @param User $member
	 * @param OrganizationMembership[] $memberships
	 * @return void
	 */
	public function sendWorkItemIdeaCreatedMail(Task $task, User $member, $memberships);
}
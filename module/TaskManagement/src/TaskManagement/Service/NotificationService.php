<?php

namespace TaskManagement\Service;

use Application\Entity\BasicUser;
use Application\Entity\User;
use People\Entity\OrganizationMembership;
use TaskManagement\Entity\Task;

interface NotificationService
{
	/**
	 * Send notification to owner
	 * @param Task $task
	 * @param User $member
	 * @return BasicUser[] receivers
	 */
	public function sendEstimationAddedInfoMail(Task $task, User $member);

	/**
	 * Send notification to owner
	 * @param Task $task
	 * @param User $member
	 * @return BasicUser[] receivers
	 */
	public function sendSharesAssignedInfoMail(Task $task, User $member);

	/**
	 * Send notification to members that haven't assigned shares yet
	 * @param Task $task
	 * @return BasicUser[] receivers
	 */
	public function remindAssignmentOfShares(Task $task);

	/**
	 * Send notification to members that haven't estimate yet
	 * @param Task $task
	 * @return BasicUser[] receivers
	 */
	public function remindEstimation(Task $task);

	/**
	 * Send notification to members
	 * @param Task $task
	 * @return BasicUser[] receivers
	 */
	public function sendTaskClosedInfoMail(Task $task);
	
	/**
	 * Send notification to members that a new Work Item Idea has been created
	 * @param Task $task
	 * @param User | NULL $member
	 * @param OrganizationMembership[] $memberships
	 * @return BasicUser[] receivers
	 */
	public function sendWorkItemIdeaCreatedMail(Task $task, $member, $memberships);
	
	/**
	 * Send notification to $task members when $task is accepted  
	 * @param Task $task
	 */
	public function sendTaskAcceptedInfoMail(Task $task);
}
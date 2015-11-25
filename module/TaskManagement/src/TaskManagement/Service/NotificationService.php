<?php

namespace TaskManagement\Service;

use Application\Entity\BasicUser;
use Application\Entity\User;
use People\Entity\Organization;
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
	 * Send import details from Kanbanize to all members of an organization
	 * @param array $result
	 * @param Organization $organization
	 */
	public function sendKanbanizeImportResultMail($result, Organization $organization);
}
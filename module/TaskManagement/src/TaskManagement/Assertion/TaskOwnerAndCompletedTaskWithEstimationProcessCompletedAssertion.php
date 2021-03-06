<?php

namespace TaskManagement\Assertion;

use Zend\Permissions\Acl\Acl;
use Zend\Permissions\Acl\Role\RoleInterface;
use Zend\Permissions\Acl\Resource\ResourceInterface;
use Zend\Permissions\Acl\Assertion\AssertionInterface;
use TaskManagement\Entity\Task;

class TaskOwnerAndCompletedTaskWithEstimationProcessCompletedAssertion implements AssertionInterface
{
	public function assert(Acl $acl, RoleInterface $user = null, ResourceInterface $resource = null, $privilege = null)
	{
		return $resource->getStatus() == Task::STATUS_COMPLETED
			&& $resource->getAverageEstimation() != null
			&& $resource->getMemberRole($user) == Task::ROLE_OWNER;
	}
}
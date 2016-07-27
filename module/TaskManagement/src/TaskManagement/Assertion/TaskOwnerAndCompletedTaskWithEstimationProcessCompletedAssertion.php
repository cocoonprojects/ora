<?php

namespace TaskManagement\Assertion;

use Zend\Permissions\Acl\Acl;
use Zend\Permissions\Acl\Role\RoleInterface;
use Zend\Permissions\Acl\Resource\ResourceInterface;
use TaskManagement\Entity\Task;

class TaskOwnerAndCompletedTaskWithEstimationProcessCompletedAssertion extends ItemOwnerAssertion
{
	public function assert(Acl $acl, RoleInterface $user = null, ResourceInterface $resource = null, $privilege = null)
	{
		return parent::assert($acl, $user, $resource, $privilege)
			&& $resource->getStatus() == Task::STATUS_COMPLETED
			&& $resource->getAverageEstimation() != null;
	}
}
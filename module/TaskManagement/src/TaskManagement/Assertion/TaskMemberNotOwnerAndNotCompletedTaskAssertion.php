<?php

namespace TaskManagement\Assertion;

use Zend\Permissions\Acl\Acl;
use Zend\Permissions\Acl\Resource\ResourceInterface;
use Zend\Permissions\Acl\Role\RoleInterface;
use TaskManagement\Entity\Task;

class TaskMemberNotOwnerAndNotCompletedTaskAssertion extends NotCompletedTaskAssertion
{
	public function assert(Acl $acl, RoleInterface $user = null, ResourceInterface $resource = null, $privilege = null)
	{
		return parent::assert($acl, $user, $resource, $privilege)
			&& $resource->getMemberRole($user != Task::ROLE_OWNER);
	}
}
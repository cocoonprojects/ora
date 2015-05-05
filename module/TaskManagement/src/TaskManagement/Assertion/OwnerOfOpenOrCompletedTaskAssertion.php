<?php
namespace TaskManagement\Assertion;

use Zend\Permissions\Acl\Acl;
use Zend\Permissions\Acl\Role\RoleInterface;
use Zend\Permissions\Acl\Resource\ResourceInterface;
use Zend\Permissions\Acl\Assertion\AssertionInterface;
use TaskManagement\Entity\Task;

class OwnerOfOpenOrCompletedTaskAssertion implements AssertionInterface
{
	public function assert(Acl $acl, RoleInterface $user = null, ResourceInterface $resource = null, $privilege = null)
	{
		return in_array($resource->getStatus(), array(Task::STATUS_OPEN, Task::STATUS_COMPLETED))
			&& $resource->getMemberRole($user) == Task::ROLE_OWNER;
	}
}
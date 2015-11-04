<?php

namespace TaskManagement\Assertion;


use Zend\Permissions\Acl\Acl;
use Zend\Permissions\Acl\Role\RoleInterface;
use Zend\Permissions\Acl\Resource\ResourceInterface;
use Zend\Permissions\Acl\Assertion\AssertionInterface;
use TaskManagement\Entity\Task;

class AcceptedTaskAndMemberSharesNotAssignedAssertion implements AssertionInterface
{
	public function assert(Acl $acl, RoleInterface $user = null, ResourceInterface $resource = null, $privilege = null)
	{
		return $resource->getStatus() == Task::STATUS_ACCEPTED
			&& $resource->hasMember($user)
			&& (!is_null($resource->areSharesAssignedFromMember($user)) && $resource->areSharesAssignedFromMember($user) == false);
	}
}
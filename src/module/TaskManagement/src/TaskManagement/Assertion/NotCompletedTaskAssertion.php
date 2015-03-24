<?php

namespace TaskManagement\Service;

use Zend\Permissions\Acl\Acl;
use Zend\Permissions\Acl\Resource\ResourceInterface;
use Zend\Permissions\Acl\Role\RoleInterface;
use Zend\Permissions\Acl\Assertion\AssertionInterface;
use Ora\ReadModel\Task;

class NotCompletedTaskAssertion implements AssertionInterface{
	
	public function assert(Acl $acl, RoleInterface $user = null, ResourceInterface $resource = null, $privilege = null){
		
		return $resource->getStatus() < Task::STATUS_COMPLETED;		
	}
}
<?php

namespace People\Assertion;

use Zend\Permissions\Acl\Acl;
use Zend\Permissions\Acl\Assertion\AssertionInterface;
use Zend\Permissions\Acl\Role\RoleInterface;
use Zend\Permissions\Acl\Resource\ResourceInterface;

class OwnerOfOrganizationAssertion implements AssertionInterface{
	
	public function assert(Acl $acl, RoleInterface $user = null, ResourceInterface $resource = null, $privilege = null)
	{
		return $user->isOwnerOf($resource);
	}
}
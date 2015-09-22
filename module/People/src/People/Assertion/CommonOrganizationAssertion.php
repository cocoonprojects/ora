<?php

namespace People\Assertion;

use Zend\Permissions\Acl\Acl;
use Zend\Permissions\Acl\Role\RoleInterface;
use Zend\Permissions\Acl\Resource\ResourceInterface;
use Zend\Permissions\Acl\Assertion\AssertionInterface;

class CommonOrganizationAssertion implements AssertionInterface
{
	public function assert(Acl $acl, RoleInterface $user = null, ResourceInterface $resource = null, $privilege = null)
	{
		foreach ($user->getOrganizationMemberships() as $m){
			foreach ($resource->getOrganizationMemberships() as $m2){
				if($m->getOrganization()===$m2->getOrganization()){
					return true;
				}
			}
		}
		return false;
	}
}
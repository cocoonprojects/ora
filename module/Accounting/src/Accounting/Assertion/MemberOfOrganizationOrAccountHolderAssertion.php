<?php

namespace Accounting\Assertion;

use Zend\Permissions\Acl\Acl;
use Zend\Permissions\Acl\Resource\ResourceInterface;
use Zend\Permissions\Acl\Role\RoleInterface;
use Accounting\Entity\OrganizationAccount;

class MemberOfOrganizationOrAccountHolderAssertion extends AccountHolderAssertion
{
	public function assert(Acl $acl, RoleInterface $user = null, ResourceInterface $resource = null, $privilege = null)
	{
		return parent::assert($acl, $user, $resource, $privilege)
			|| ($resource instanceof OrganizationAccount && $user->isMemberOf($resource->getOrganization()));
	}
	
}
<?php
namespace TaskManagement\Assertion;

use Zend\Permissions\Acl\Acl;
use Zend\Permissions\Acl\Role\RoleInterface;
use Zend\Permissions\Acl\Resource\ResourceInterface;

class ItemOwnerAndItemCreatedInTheLast24HoursAssertion extends ItemOwnerAssertion {
	public function assert(Acl $acl, RoleInterface $user = null, ResourceInterface $resource = null, $privilege = null) {
		return parent::assert($acl, $user, $resource, $privilege) && 
			(new \DateTime() <= $resource->getCreatedAt()->modify('+24 hours'));
	}
}
<?php
namespace TaskManagement\Assertion;

use Zend\Permissions\Acl\Acl;
use Zend\Permissions\Acl\Role\RoleInterface;
use Zend\Permissions\Acl\Resource\ResourceInterface;

class ItemOwnerAndNotExpiredItemDeletionAssertion extends ItemOwnerAssertion {
	public function assert(Acl $acl, RoleInterface $user = null, ResourceInterface $resource = null, $privilege = null) {
		$ref = clone($resource->getCreatedAt());
		return parent::assert($acl, $user, $resource, $privilege) && 
			new \DateTime() <= $ref->modify('+24 hours');
	}
}
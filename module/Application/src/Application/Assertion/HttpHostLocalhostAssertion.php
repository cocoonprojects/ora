<?php

namespace Application\Assertion;

use Zend\Permissions\Acl\Acl;
use Zend\Permissions\Acl\Role\RoleInterface;
use Zend\Permissions\Acl\Resource\ResourceInterface;
use Zend\Permissions\Acl\Assertion\AssertionInterface;

class HttpHostLocalhostAssertion implements AssertionInterface
{
	public function assert(Acl $acl, RoleInterface $user = null, ResourceInterface $resource = null, $privilege = null){
			
		return isset($_SERVER['HTTP_HOST']) && $_SERVER['HTTP_HOST'] == 'localhost';
	}
}
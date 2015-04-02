<?php

namespace Accounting\Assertion;


use Zend\Permissions\Acl\Acl;
use Zend\Permissions\Acl\Resource\ResourceInterface;
use Zend\Permissions\Acl\Role\RoleInterface;
use Ora\User\User;
use Ora\ReadModel\OrganizationAccount;

class MemberOfOrganizationOrAccountHolderAssertion extends AccountHolderAssertion
{
    public function assert(Acl $acl, RoleInterface $role = null, ResourceInterface $resource = null, $privilege = null){
    	
    	return ( parent::assert($acl, $role, $resource, $privilege) || 
    				($resource instanceof OrganizationAccount && $this->loggedUser->isMemberOf($resource->getOrganization()))	
    			);    	
    }
    
}
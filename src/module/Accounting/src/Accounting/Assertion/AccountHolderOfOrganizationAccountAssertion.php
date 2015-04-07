<?php

namespace Accounting\Assertion;

use Zend\Permissions\Acl\Acl;
use Zend\Permissions\Acl\Resource\ResourceInterface;
use Zend\Permissions\Acl\Role\RoleInterface;
use Zend\Permissions\Acl\Assertion\AssertionInterface;
use Application\Entity\User;
use Accounting\Entity\OrganizationAccount;

class AccountHolderOfOrganizationAccountAssertion implements AssertionInterface{

	protected $loggedUser;
    
	public function setLoggedUser($loggedUser = null) {
    	$this->loggedUser = $loggedUser;
    }
	
	
	public function assert(Acl $acl, RoleInterface $role = null, ResourceInterface $resource = null, $privilege = null){
		
		if($this->loggedUser instanceof User){
			
			if(!($resource instanceof OrganizationAccount)) {
				return false;
			}
						
			return $resource->isHeldBy($this->loggedUser);	
		}
		
		return false;
				
	}
}
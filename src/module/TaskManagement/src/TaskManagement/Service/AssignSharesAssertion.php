<?php

namespace TaskManagement\Service;


use Zend\Permissions\Acl\Acl;
use Zend\Permissions\Acl\Resource\ResourceInterface;
use Zend\Permissions\Acl\Role\RoleInterface;
use Zend\Permissions\Acl\Assertion\AssertionInterface;
use Ora\User\User;

class AssignSharesAssertion implements AssertionInterface
{
    private $loggedUser;
    
    public function __construct(User $loggedUser = null) {
        $this->loggedUser  = $loggedUser;                       
    }
    
	public function assert(Acl $acl, RoleInterface $role = null, ResourceInterface $resource = null, $privilege = null){
		
		if($this->loggedUser instanceof User){
			
			if(!$resource->hasMember($this->loggedUser)){
				return false;    		
			}
			
			if($resource->getStatus() != $resource::STATUS_ACCEPTED) {
    			return false;
    		}
    		
			return true;			
						
    	}else{
    		return false;    	
    	}
    }
    
}
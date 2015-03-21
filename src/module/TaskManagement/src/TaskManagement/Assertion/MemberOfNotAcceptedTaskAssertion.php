<?php

namespace TaskManagement\Assertion;

use Zend\Permissions\Acl\Acl;
use Zend\Permissions\Acl\Resource\ResourceInterface;
use Zend\Permissions\Acl\Role\RoleInterface;
use Zend\Permissions\Acl\Assertion\AssertionInterface;
use Ora\User\User;
use Ora\ReadModel\Task;

class MemberOfNotAcceptedTaskAssertion implements AssertionInterface
{
    private $loggedUser;
    
    public function __construct(User $loggedUser = null) {
        $this->loggedUser  = $loggedUser;
    }
    
	public function assert(Acl $acl, RoleInterface $role = null, ResourceInterface $resource = null, $privilege = null){
		
		if($resource->getStatus() < Task::STATUS_ACCEPTED){
			
			if($this->loggedUser instanceof User){
			
				if($resource->hasMember($this->loggedUser->getId())){
					return true;
				}
			}					    
    	}
    	
    	return false;
    }
    
}
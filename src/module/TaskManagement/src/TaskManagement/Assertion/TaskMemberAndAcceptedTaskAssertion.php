<?php

namespace TaskManagement\Assertion;


use Zend\Permissions\Acl\Acl;
use Zend\Permissions\Acl\Resource\ResourceInterface;
use Zend\Permissions\Acl\Role\RoleInterface;
use Zend\Permissions\Acl\Assertion\AssertionInterface;
use Application\Entity\User;
use TaskManagement\Entity\Task;

class TaskMemberAndAcceptedTaskAssertion implements AssertionInterface
{
    private $loggedUser;
    
    public function setLoggedUser($loggedUser = null) {
    	$this->loggedUser = $loggedUser;
    }
    
	public function assert(Acl $acl, RoleInterface $role = null, ResourceInterface $resource = null, $privilege = null){
		
		if($resource->getStatus() == Task::STATUS_ACCEPTED) {
		
			if($this->loggedUser instanceof User){
				
				if($resource->hasMember($this->loggedUser)){
					return true;    		
				}	
    		}  
    	}
    	return false;  
    }
    
}
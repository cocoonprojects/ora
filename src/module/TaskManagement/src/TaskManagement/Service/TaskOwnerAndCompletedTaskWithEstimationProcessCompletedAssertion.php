<?php

namespace TaskManagement\Service;


use Zend\Permissions\Acl\Acl;
use Zend\Permissions\Acl\Resource\ResourceInterface;
use Zend\Permissions\Acl\Role\RoleInterface;
use Zend\Permissions\Acl\Assertion\AssertionInterface;
use Ora\User\User;
use Ora\ReadModel\Task;

class TaskOwnerAndCompletedTaskWithEstimationProcessCompletedAssertion implements AssertionInterface
{
    private $loggedUser;
    
    public function __construct(User $loggedUser = null) {
        $this->loggedUser  = $loggedUser;                       
    }
    
	public function assert(Acl $acl, RoleInterface $role = null, ResourceInterface $resource = null, $privilege = null){
		
		if($resource->getStatus() == Task::STATUS_COMPLETED) {
			
			if($this->loggedUser instanceof User){

				if(!is_null($resource->getAverageEstimation())){
				
					if($resource->hasMember($this->loggedUser)){
						
						$roleMember = $resource->getMemberRole($this->loggedUser->getId());
						if($roleMember == Task::ROLE_OWNER){
							
							return true;
						}						
					}					
				}				
    		}
    	}    	
    	return false;
    }
    
}
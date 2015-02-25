<?php

namespace TaskManagement\Service;


use Zend\Permissions\Acl\Acl;
use Zend\Permissions\Acl\Resource\ResourceInterface;
use Zend\Permissions\Acl\Role\RoleInterface;
use Zend\Permissions\Acl\Assertion\AssertionInterface;
use Ora\User\User;
use Ora\IllegalStateException;
use Ora\InvalidArgumentException;
use Ora\ReadModel\Task as ReadModelTask;

class CompleteTaskAssertion implements AssertionInterface
{
    private $loggedUser;
    
    public function __construct(User $loggedUser = null) {
        $this->loggedUser  = $loggedUser;                       
    }
    
	public function assert(Acl $acl, RoleInterface $role = null, ResourceInterface $resource = null, $privilege = null){
		
		if($this->loggedUser instanceof User){

			$roleMember = $resource->getMemberRole($this->loggedUser->getId());
			
			if(!in_array($resource->getStatus(), array($resource::STATUS_ONGOING, $resource::STATUS_ACCEPTED))) {
    			return false;
    			//throw new IllegalStateException('Cannot complete a task in '.$resource->getStatus().' state. Task '.$resource->getReadableId().' won\'t be completed', 403);
    		}
    		
			if(!$resource->hasMember($this->loggedUser)){
				return false;
    			//throw new InvalidArgumentException('You\'re not member owner of this task. Task '.$resource->getReadableId().' won\'t be completed', 403);
			}
								
			if($roleMember != $resource::ROLE_OWNER){
				return false;
    			//throw new InvalidArgumentException('Only the owner of the task can complete it. Task '.$resource->getReadableId().' won\'t be completed', 403);
			}				

			return true;			
						
    	}else{
    		return false;    	
    		//throw new InvalidArgumentException('Logged user not found', 401);
    	}
    }
    
}
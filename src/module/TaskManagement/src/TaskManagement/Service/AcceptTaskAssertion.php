<?php

namespace TaskManagement\Service;


use Zend\Permissions\Acl\Acl;
use Zend\Permissions\Acl\Resource\ResourceInterface;
use Zend\Permissions\Acl\Role\RoleInterface;
use Zend\Permissions\Acl\Assertion\AssertionInterface;
use Ora\User\User;

class AcceptTaskAssertion implements AssertionInterface
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
			
			$roleMember = $resource->getMemberRole($this->loggedUser->getId());
			if($roleMember != $resource::ROLE_OWNER){
				return false;
			}
			
			if($resource->getStatus() != $resource::STATUS_COMPLETED) {
    			return false;
    		}
    		
			if(is_null($resource->getEstimation())){
				return false;
			}
								
			return true;			
						
    	}else{
    		return false;    	
    	}
    }
    
}
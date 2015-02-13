<?php

namespace TaskManagement\Service;


use Zend\Permissions\Acl\Acl;
use Zend\Permissions\Acl\Resource\ResourceInterface;
use Zend\Permissions\Acl\Role\RoleInterface;
use Zend\Permissions\Acl\Assertion\AssertionInterface;
use Ora\User\User;

class DeleteTaskAssertion implements AssertionInterface
{
    private $loggedUser;
    
    public function __construct(User $loggedUser = null) {
        $this->loggedUser  = $loggedUser;                       
    }
    
	public function assert(Acl $acl, RoleInterface $role = null, ResourceInterface $resource = null, $privilege = null){
		
		if($this->loggedUser instanceof User){

			$resourceStatus = $resource->getStatus();
			$loggedUserId = $this->loggedUser->getId();
			$roleMember = $resource->getMemberRole($this->loggedUser->getId());

			return $roleMember == $resource::ROLE_OWNER && $resourceStatus == $resource::STATUS_ONGOING;
						
    	}else{    	
    		return false;
    	}
    }
    
}
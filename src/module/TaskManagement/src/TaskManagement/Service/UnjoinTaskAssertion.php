<?php

namespace TaskManagement\Service;


use Zend\Permissions\Acl\Acl;
use Zend\Permissions\Acl\Resource\ResourceInterface;
use Zend\Permissions\Acl\Role\RoleInterface;
use Zend\Permissions\Acl\Assertion\AssertionInterface;
use Ora\User\User;

class UnjoinTaskAssertion implements AssertionInterface
{
    private $loggedUser;
    
    public function __construct(User $loggedUser = null) {
        $this->loggedUser  = $loggedUser;
    }
    
	public function assert(Acl $acl, RoleInterface $role = null, ResourceInterface $resource = null, $privilege = null){
		
		if($this->loggedUser instanceof User){

			$loggedUserId = $this->loggedUser->getId();			
			$roleMember = $resource->getMemberRole($this->loggedUser->getId());
			
			if($roleMember == $resource::ROLE_OWNER || $roleMember == $resource::NOT_MEMBER){
				return false;
			}			
			return true;
		}
    }    
}
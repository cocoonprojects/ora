<?php

namespace TaskManagement\Service;


use Zend\Permissions\Acl\Acl;
use Zend\Permissions\Acl\Resource\ResourceInterface;
use Zend\Permissions\Acl\Role\RoleInterface;
use Zend\Permissions\Acl\Assertion\AssertionInterface;
use Ora\User\User;

class JoinTaskAssertion implements AssertionInterface
{
    private $loggedUser;
    
    public function __construct(User $loggedUser = null) {
        $this->loggedUser  = $loggedUser;          
    }
    
	public function assert(Acl $acl, RoleInterface $role = null, ResourceInterface $resource = null, $privilege = null){
		
		//TODO: manca sul WriteModel la possibilita' di accedere, dalla risorsa Task, allo Stream associato.
		//      Al momento questa assertion e' usata solamente per il ReadModel
		if($this->loggedUser instanceof User){
    		
		    $currentOrganizationId = $resource->getStream()->getReadableOrganization();		    
		    
		    if($this->loggedUser->isMemberOf($currentOrganizationId) && !$resource->hasMember($this->loggedUser)){
		    	if($resource->getStatus() >= $resource::STATUS_COMPLETED){
		    		return false;
		    	}
		    	return true;		    	

		    }else{
		    	return false;
		    }
    	}else{

    		return false;
    	}
    }
}
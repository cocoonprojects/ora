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
    private $organizationMemberships;
    
    public function __construct($organizationMemberships, User $loggedUser = null) {
        $this->loggedUser  = $loggedUser;
        $this->organizationMemberships = $organizationMemberships;               
    }
    
	public function assert(Acl $acl, RoleInterface $role = null, ResourceInterface $resource = null, $privilege = null){

		//TODO: manca sul WriteModel la possibilita' di accedere, dalla risorsa Task, allo Stream associato. 
		//      Al momento questa assertion e' usata solamente per il ReadModel
		
		if($this->loggedUser instanceof User){
    		
			$taskStatus = $resource->getStatus();
		    $currentStream = $resource->getStream();		    
		    $currentOrganizationId = $currentStream->getReadableOrganization();		    
		    $taskMembers = $resource->getReadableMembers();
		    
		    $loggedUserIsTaskMember = array_key_exists($this->loggedUser->getId(), $taskMembers);
		    
			if($taskStatus == $resource::STATUS_ONGOING && !$loggedUserIsTaskMember){
				foreach ($this->organizationMemberships as $membership){
					
			    	$organizationMembershipId = $membership->getOrganization()->getId();
			    	
			    	if($currentOrganizationId == $organizationMembershipId){		    		
			    		return true;
			    	}
			    }
			    return false;
			}else{
				return false;
			}
					    
    	}else{    	
    		return false;
    	}
    }
    
}
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
    
    //imposto il default a null su $loggedUser
    //se la richiesta arriva senza che l'utente sia loggato
    public function __construct($organizationMemberships, User $loggedUser = null) {
        $this->loggedUser  = $loggedUser;
        $this->organizationMemberships = $organizationMemberships;               
    }
    
	public function assert(Acl $acl, RoleInterface $role = null, ResourceInterface $resource = null, $privilege = null){
		
		if($this->loggedUser instanceof User){
    		
			$taskStatus = $resource->getStatus();

			//controllo se lo stream, relativo al task sul quale e' stato richiesto il JOIN, e' associato all'organizzazione 
		    //dell'utente loggato
		    $currentStreamId = $resource->getStreamId();
		    
		    //MI MANCA IL COLLEGAMENTO DA $currentStreamId A $currentStream
		    
		    $currentOrganizationId = $currentStream->getReadableOrganization();

			if($taskStatus == $resource::STATUS_ONGOING){
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
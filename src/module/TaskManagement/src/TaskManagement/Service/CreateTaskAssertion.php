<?php

namespace TaskManagement\Service;


use Zend\Permissions\Acl\Acl;
use Zend\Permissions\Acl\Resource\ResourceInterface;
use Zend\Permissions\Acl\Role\RoleInterface;
use Zend\Permissions\Acl\Assertion\AssertionInterface;
use Ora\User\User;

class CreateTaskAssertion implements AssertionInterface
{
    private $loggedUser;
    
    //imposto il default a null su $loggedUser se la richiesta arriva senza che l'utente sia loggato
    public function __construct(User $loggedUser = null) {
        $this->loggedUser  = $loggedUser;             
    }
    
	public function assert(Acl $acl, RoleInterface $role = null, ResourceInterface $resource = null, $privilege = null){

		if($this->loggedUser instanceof User){
			
		    //controllo se lo stream nel quale creare il task e' associato all'organizzazione dell'utente loggato
			$currentOrganizationId = $resource->getReadableOrganization();

			if($this->loggedUser->isMemberOf($currentOrganizationId)){
				return true;
			}
			
		    return false;		    
    	}else{    	
    		return false;
    	}
    }
    
}
<?php

namespace TaskManagement\Service;


use Zend\Permissions\Acl\Acl;
use Zend\Permissions\Acl\Resource\ResourceInterface;
use Zend\Permissions\Acl\Role\RoleInterface;
use Zend\Permissions\Acl\Assertion\AssertionInterface;
use Ora\User\User;
use Ora\ReadModel\Stream;

class MemberOfOrganizationAssertion implements AssertionInterface
{
    private $loggedUser;
    
	public function setLoggedUser($loggedUser = null) {
    	$this->loggedUser = $loggedUser;
    }
    
	public function assert(Acl $acl, RoleInterface $role = null, ResourceInterface $resource = null, $privilege = null){

		if($this->loggedUser instanceof User){			

			//controllo se il task per il quale visualizzare il dettaglio 
			//appartiene ad uno stream gestito dalla stessa organizzazione dell'utente loggato
			$stream = $resource->getStream();			
			if($stream instanceof Stream){
				return $this->loggedUser->isMemberOf($stream->getOrganization());	
			}			
    	}    	
    	return false;
    }
    
}
<?php

namespace TaskManagement\Service;


use Zend\Permissions\Acl\Acl;
use Zend\Permissions\Acl\Resource\ResourceInterface;
use Zend\Permissions\Acl\Role\RoleInterface;
use Zend\Permissions\Acl\Assertion\AssertionInterface;
use Ora\User\User;

class MemberOfOrganizationAssertion implements AssertionInterface
{
    private $loggedUser;
    
	public function setLoggedUser($loggedUser = null) {
    	$this->loggedUser = $loggedUser;
    }
    
	public function assert(Acl $acl, RoleInterface $role = null, ResourceInterface $resource = null, $privilege = null){

		if($this->loggedUser instanceof User){			

			//controllo se lo stream nel quale creare il task e' associato all'organizzazione dell'utente loggato
			return $this->loggedUser->isMemberOf($resource->getOrganization());			
    	}    	
    	return false;
    }
    
}
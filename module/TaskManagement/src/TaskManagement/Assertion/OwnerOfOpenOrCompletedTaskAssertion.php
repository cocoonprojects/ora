<?php
namespace TaskManagement\Assertion;

use Zend\Permissions\Acl\Acl;
use Zend\Permissions\Acl\Resource\ResourceInterface;
use Zend\Permissions\Acl\Role\RoleInterface;
use Zend\Permissions\Acl\Assertion\AssertionInterface;
use Application\Entity\User;
use TaskManagement\Entity\Task;

class OwnerOfOpenOrCompletedTaskAssertion implements AssertionInterface
{
    private $loggedUser;
    
	public function setLoggedUser($loggedUser = null) {
    	$this->loggedUser = $loggedUser;
    }
    
	public function assert(Acl $acl, RoleInterface $role = null, ResourceInterface $resource = null, $privilege = null){
		if(in_array($resource->getStatus(), array(Task::STATUS_OPEN, Task::STATUS_COMPLETED))) {
			if($this->loggedUser instanceof User){
	    		if($resource->getMemberRole($this->loggedUser) == Task::ROLE_OWNER){
					return true;
	    		}    					
			}
		}
    	
    	return false;
    }
    
}
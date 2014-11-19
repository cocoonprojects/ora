<?php

namespace Ora\Organization;

use Ora\EventStore\EventStore;
use Ora\UserOrganization\UserOrganization;
use Ora\User\User;

class EventSourcingOrganizationService implements OrganizationService
{
    private $entityManager;
    private $eventStore;
    
    public function __construct($entityManager, EventStore $eventStore, $entitySerializer)
    {
        $this->entityManager = $entityManager;
        $this->eventStore = $eventStore;
	    $this->entitySerializer = $entitySerializer;
    }
	
    public function addUser(Organization $organization, User $user)
    {
    	$createdAt = new \DateTime();
    	
    	
    	$userOrganization = new UserOrganization($createdAt, $user, $user, $organization, UserOrganization::$organizationRoleMap[ROLE_MEMBER]);
    	
    	$event = new AddUserToOrganizationEvent($createdAt, $userOrganization, $this->entitySerializer);
    	 
    	$this->eventStore->appendToStream($event);
    }

	/**
	*  Retrieve organization entity with specified ID
	*/
	public function findOrganization($id)
	{
	    $organization = $this->entityManager->getRepository('Ora\Organization\Organization')->findOneBy(array("id"=>$id));
	     
	    return $organization;
	}
}

<?php

namespace Ora\Organization;

use Ora\EventStore\EventStore;
use Ora\EntitySerializer;
use Ora\UserOrganization\UserOrganization;

/**
 * @author Giannotti Fabio
 */
class EventSourcingOrganizationService implements OrganizationService
{
    private $entityManager;
    private $eventStore;
    private $entitySerializer;
    
    public function __construct($entityManager, EventStore $eventStore, EntitySerializer $entitySerializer)
    {
        $this->entityManager = $entityManager;
        $this->eventStore = $eventStore;
	    $this->entitySerializer = $entitySerializer;
    }
	
    public function addUser(Organization $organization, User $user)
    {
    	//new UserOrganization(\DateTime $createdAt, $createdBy, $user, $organization, $organizationRole);
    }
	/**
	 * Retrieve organization entity with specified ID
	 */
	public function findOrganization($id)
	{
	    $organization = $this->entityManager->getRepository('Ora\Organization\Organization')->findOneBy(array("id"=>$id));
	     
	    return $organization;
	}
}

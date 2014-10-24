<?php

namespace Ora\Organization;

use Ora\EventStore\EventStore;
use Ora\EntitySerializer;


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
	
	/**
	 * Retrieve organization entity with specified ID
	 */
	public function findOrganization($id)
	{
	    $organization = $this->entityManager->getRepository('Ora\Organization\Organization')->findOneBy(array("id"=>$id));
	     
	    return $organization;
	}
}
<?php

namespace Ora\ProjectManagement;

use Ora\EventStore\EventStore;
use Ora\EntitySerializer;

/**
 * @author Giannotti Fabio
 */
class EventSourcingProjectService implements ProjectService
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
	
	public function findProjectByID($projectID)
	{
	    $project = $this->entityManager->getRepository('Ora\ProjectManagement\Project')->findOneBy(array("id"=>$projectID));
	    
	    return $project;
	}
} 
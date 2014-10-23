<?php

namespace Ora\User;

use Ora\EventStore\EventStore;
use Ora\EntitySerializer;


/**
 * @author Giannotti Fabio
 */
class EventSourcingUserService implements UserService
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
	 * Retrieve user entity with specified ID
	 */
	public function findUserByID($id)
	{
	    $user = $this->entityManager->getRepository('Ora\User\User')->findOneBy(array("id"=>$id));
	     
	    return $user;
	}
}
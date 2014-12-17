<?php

namespace Ora\Organization;

use Doctrine\ORM\EntityManager;
use Prooph\EventStore\EventStore;
use Prooph\EventStore\Aggregate\AggregateRepository;
use Prooph\EventStore\Stream\StreamStrategyInterface;
use Prooph\EventSourcing\EventStoreIntegration\AggregateTranslator;
use Prooph\EventStore\Aggregate\AggregateType;
use Ora\IllegalStateException;
use Ora\User\User;
use Ora\ReadModel\Organization as ReadModelOrganization;

class EventSourcingOrganizationService extends AggregateRepository implements OrganizationService
{
    private $entityManager;
    
    public function __construct(EventStore $eventStore, StreamStrategyInterface $eventStoreStrategy, EntityManager $entityManager)
    {
		parent::__construct($eventStore, new AggregateTranslator(), $eventStoreStrategy, new AggregateType('Ora\TaskManagement\Task'));
		$this->entityManager = $entityManager;
    }
	
	/**
	*  Retrieve organization entity with specified ID
	*/
	public function findOrganization($id)
	{
	    $organization = $this->entityManager
	                         ->getRepository('Ora\ReadModel\Organization')
	    					 ->findOneBy(array("id"=>$id));
	     
	    return $organization;
	}
	
	public function findUserOrganizationMembership(User $user)
	{
		$organization = array();
		
		$organizationMembership = $this->entityManager
		            			 ->getRepository('Ora\ReadModel\OrganizationMembership')
								  ->findBy(array('user' => $user));
				
		return $organizationMembership;
	}	
	
	public function findOrganizationMembership(Organization $organization)
	{
		
	}
}

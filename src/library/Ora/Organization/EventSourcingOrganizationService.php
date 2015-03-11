<?php

namespace Ora\Organization;

use Zend\EventManager\EventManager;
use Zend\EventManager\EventManagerInterface;
use Doctrine\ORM\EntityManager;
use Prooph\EventStore\EventStore;
use Prooph\EventStore\Aggregate\AggregateRepository;
use Prooph\EventStore\Aggregate\AggregateType;
use Prooph\EventStore\Stream\SingleStreamStrategy;
use Prooph\EventSourcing\EventStoreIntegration\AggregateTranslator;
use Ora\User\User;
use Ora\ReadModel\Organization as OrganizationReadModel;

class EventSourcingOrganizationService extends AggregateRepository implements OrganizationService
{
	/**
	 * 
	 * @var EventManagerInterface
	 */
    protected $events;
    /**
     * 
     * @var EntityManager
     */
    protected $entityManager;
    
    public function __construct(EventStore $eventStore, EntityManager $entityManager)
    {
		parent::__construct($eventStore, new AggregateTranslator(), new SingleStreamStrategy($eventStore), new AggregateType('Ora\Organization\Organization'));
		$this->entityManager = $entityManager;
    }
    
	public function createOrganization($name, User $createdBy) {
		$this->eventStore->beginTransaction();
		$org = Organization::create($name, $createdBy);
		$this->addAggregateRoot($org);
		$this->eventStore->commit();
		$this->getEventManager()->trigger('OrganizationService.OrganizationCreated', $this, [$org, $createdBy]);
		return $org;
	}
	
    public function getOrganization($id) {
    	try {
    		$rv = $this->getAggregateRoot($this->aggregateType, $id);
    		return $rv;
    	} catch (\RuntimeException $e) {
    		return null;
    	}
    }
	
	/**
	*  Retrieve organization entity with specified ID
	*/
	public function findOrganization($id)
	{
	    return $this->entityManager->find('Ora\ReadModel\Organization', $id);
	}
	
	public function findUserOrganizationMemberships(User $user)
	{
		return $this->entityManager->getRepository('Ora\ReadModel\OrganizationMembership')->findBy(array('member' => $user));
	}	
	
	public function findOrganizationMemberships(OrganizationReadModel $organization)
	{
		return $this->entityManager->getRepository('Ora\ReadModel\OrganizationMembership')->findBy(array('organization' => $organization));
	}
	
    public function setEventManager(EventManagerInterface $events)
    {
        $events->setIdentifiers(array(
            __CLASS__,
            get_class($this)
        ));
        $this->events = $events;
    }

    public function getEventManager()
    {
        if (!$this->events) {
            $this->setEventManager(new EventManager());
        }
        return $this->events;
    }
}

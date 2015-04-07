<?php

namespace Application\Service;

use Zend\EventManager\EventManager;
use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\EventManagerAwareInterface;
use Doctrine\ORM\EntityManager;
use Prooph\EventStore\EventStore;
use Prooph\EventStore\Aggregate\AggregateRepository;
use Prooph\EventStore\Aggregate\AggregateType;
use Prooph\EventStore\Stream\SingleStreamStrategy;
use Prooph\EventSourcing\EventStoreIntegration\AggregateTranslator;
use Ora\User\User;
use Application\Organization;
use Ora\ReadModel\Organization as OrganizationReadModel;
use Rhumsaa\Uuid\Uuid;

class EventSourcingOrganizationService extends AggregateRepository implements OrganizationService, EventManagerAwareInterface
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
		parent::__construct($eventStore, new AggregateTranslator(), new SingleStreamStrategy($eventStore), AggregateType::fromAggregateRootClass(Organization::class));
		$this->entityManager = $entityManager;
    }
    
	public function createOrganization($name, User $createdBy) {
		$this->eventStore->beginTransaction();
		try {
			$org = Organization::create($name, $createdBy);
			$this->addAggregateRoot($org);
			$this->eventStore->commit();
		} catch (\Exception $e) {
			$this->eventStore->rollback();
			throw $e;
		}
		$this->getEventManager()->trigger(Organization::EVENT_CREATED, $org, ['by' => $createdBy]);
		return $org;
	}
	
    public function getOrganization($id) {
		$oId = $id instanceof Uuid ? $id->toString() : $id;
		return $this->getAggregateRoot($oId);
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
        	'Application\OrganizationService',
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

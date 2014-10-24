<?php

namespace Organization\Service;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

use Ora\Organization\EventSourcingOrganizationService;
use Ora\EntitySerializer;

class OrganizationServiceFactory implements FactoryInterface 
{
    private static $instance;
    
	public function createService(ServiceLocatorInterface $serviceLocator) 
	{
	    if(is_null(self::$instance)) 
	    {
            $eventStore = $serviceLocator->get('Application\Service\EventStore');

            $entityManager = $serviceLocator->get('doctrine.entitymanager.orm_default');
            $entitySerializer = new EntitySerializer($entityManager);
            
            self::$instance = new EventSourcingOrganizationService($entityManager, $eventStore, $entitySerializer);            
        }

	    return self::$instance;
	}
}
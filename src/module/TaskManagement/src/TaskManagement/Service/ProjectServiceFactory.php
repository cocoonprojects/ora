<?php

namespace ProjectManagement\Service;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

use Ora\ProjectManagement\EventSourcingProjectService;
use Ora\EntitySerializer;

class ProjectServiceFactory implements FactoryInterface 
{
    /**
     * @var EventSourcingProjectService
     */
    private static $instance;
    
	public function createService(ServiceLocatorInterface $serviceLocator)
	{
	    if(is_null(self::$instance)) 
	    {
            $eventStore = $serviceLocator->get('Application\Service\EventStore');
            
            $entityManager = $serviceLocator->get('doctrine.entitymanager.orm_default');
            $entitySerializer = new EntitySerializer($entityManager);
            
            self::$instance = new EventSourcingProjectService($entityManager, $eventStore, $entitySerializer);            
        }

	    return self::$instance;
	}
}
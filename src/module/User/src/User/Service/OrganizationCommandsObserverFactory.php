<?php

namespace User\Service;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class OrganizationCommandsObserverFactory implements FactoryInterface 
{
    /**
     * @var OrganizationCommandsObserver
     */
    private static $instance;
    
	public function createService(ServiceLocatorInterface $serviceLocator) 
	{
	    if(is_null(self::$instance)) 
	    {
			$entityManager = $serviceLocator->get('doctrine.entitymanager.orm_default');
            $service = new OrganizationCommandsObserver($entityManager);
            
			$eventStore = $serviceLocator->get('prooph.event_store');
			$service->observe($eventStore);
            self::$instance = $service;
	    }
	    return self::$instance;
	}
}
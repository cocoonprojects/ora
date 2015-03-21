<?php

namespace TaskManagement\Service;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class StreamCommandsObserverFactory implements FactoryInterface 
{
    /**
     * @var StreamCommandsObserver
     */
    private static $instance;
    
	public function createService(ServiceLocatorInterface $serviceLocator) 
	{
	    if(is_null(self::$instance)) 
	    {
			$entityManager = $serviceLocator->get('doctrine.entitymanager.orm_default');
            $service = new StreamCommandsObserver($entityManager);
            
			$eventStore = $serviceLocator->get('prooph.event_store');
			$service->observe($eventStore);
            self::$instance = $service;
	    }
	    return self::$instance;
	}
}
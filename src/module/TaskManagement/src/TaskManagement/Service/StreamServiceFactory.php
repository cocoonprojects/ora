<?php

namespace TaskManagement\Service;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class StreamServiceFactory implements FactoryInterface 
{
    private static $instance;
    
	public function createService(ServiceLocatorInterface $serviceLocator)
	{
	    if(is_null(self::$instance)) 
	    {
 			$eventStore = $serviceLocator->get('prooph.event_store');
	    	$entityManager = $serviceLocator->get('doctrine.entitymanager.orm_default');
	    	self::$instance = new EventSourcingStreamService($eventStore, $entityManager);
        }
	    return self::$instance;
	}
}
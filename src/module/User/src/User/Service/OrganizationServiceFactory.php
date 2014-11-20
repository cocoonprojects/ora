<?php

namespace User\Service;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

use Ora\Organization\MockOrganizationService;

use Ora\Organization\EventSourcingOrganizationService;

class OrganizationServiceFactory implements FactoryInterface 
{
    private static $instance;
    
	public function createService(ServiceLocatorInterface $serviceLocator) 
	{
	    if(is_null(self::$instance)) 
	    {
			//$eventStore = $serviceLocator->get('prooph.event_store');
			//$eventStoreStrategy = $serviceLocator->get('prooph.event_store.single_stream_strategy');
			//$entityManager = $serviceLocator->get('doctrine.entitymanager.orm_default');
			
            //self::$instance = new EventSourcingOrganizationService($eventStore, $eventStoreStrategy, $entityManager);
	    	$userService = $serviceLocator->get('User\UserService');
	    	self::$instance = new MockOrganizationService($userService);
        }
	    return self::$instance;
	}
}

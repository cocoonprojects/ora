<?php

namespace TaskManagement\Service;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

use Ora\StreamManagement\MockStreamService;
use Ora\StreamManagement\EventSourcingStreamService;
use Ora\StreamManagement\StreamService;

class StreamServiceFactory implements FactoryInterface 
{
    /**
     * @var EventSourcingStreamService
     */
    private static $instance;
    
	public function createService(ServiceLocatorInterface $serviceLocator)
	{
	    if(is_null(self::$instance)) 
	    {
 			//$eventStore = $serviceLocator->get('prooph.event_store');
 			//$eventStoreStrategy = $serviceLocator->get('prooph.event_store.single_stream_strategy');
            // self::$instance = new EventSourcingStreamService($eventStore, $eventStoreStrategy);
	    	$entityManager = $serviceLocator->get('doctrine.entitymanager.orm_default');
			$userService = $serviceLocator->get('User\UserService');
	    	self::$instance = new MockStreamService($userService, $entityManager);
        }
	    return self::$instance;
	}
}
<?php

namespace TaskManagement\Service;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

use Ora\ProjectManagement\MockProjectService;
use Ora\ProjectManagement\EventSourcingProjectService;
use Ora\ProjectManagement\ProjectService;

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
 			//$eventStore = $serviceLocator->get('prooph.event_store');
 			//$eventStoreStrategy = $serviceLocator->get('prooph.event_store.single_stream_strategy');
            // self::$instance = new EventSourcingProjectService($eventStore, $eventStoreStrategy);
	    	$entityManager = $serviceLocator->get('doctrine.entitymanager.orm_default');
			$userService = $serviceLocator->get('User\UserService');
	    	self::$instance = new MockProjectService($userService, $entityManager);
        }
	    return self::$instance;
	}
}
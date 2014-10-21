<?php

namespace User\Service;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Ora\User\EventSourcingUserService;
use Ora\EntitySerializer;

class UserServiceFactory implements FactoryInterface 
{
    /**
     * @var EventSourcingUserService
     */
    private static $instance;
    
	public function createService(ServiceLocatorInterface $serviceLocator) 
	{
	    if(is_null(self::$instance)) 
	    {
            $eventStore = $serviceLocator->get('Application\Service\EventStore');

            $entityManager = $serviceLocator->get('doctrine.entitymanager.orm_default');
            $entitySerializer = new EntitySerializer($entityManager);
            $authenticationService = $serviceLocator->get('Application\Service\AuthenticationService');
            
            self::$instance = new EventSourcingUserService($entityManager, $eventStore, $entitySerializer, $authenticationService);            
        }

	    return self::$instance;
	}
}
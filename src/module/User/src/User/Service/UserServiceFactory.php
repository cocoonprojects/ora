<?php

namespace User\Service;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

use Ora\User\UserService;
use Ora\User\EventSourcingUserService;

class UserServiceFactory implements FactoryInterface 
{
    /**
     * @var UserService
     */
    private static $instance;
    
	public function createService(ServiceLocatorInterface $serviceLocator) 
	{
	    if(is_null(self::$instance)) 
	    {
			$eventStore = $serviceLocator->get('prooph.event_store');
			$eventStoreStrategy = $serviceLocator->get('prooph.event_store.single_stream_strategy');
			$entityManager = $serviceLocator->get('doctrine.entitymanager.orm_default');
			$accountService = $serviceLocator->get('Accounting\CreditsAccountsService');
	    	$e = new EventSourcingUserService($eventStore, $eventStoreStrategy, $entityManager);
	    	$e->setAccountService($accountService);
	    	self::$instance = $e;        
        }
	    return self::$instance;
	}
}
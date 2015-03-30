<?php
namespace User\Service;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Ora\User\EventSourcingUserService;

class UserServiceFactory implements FactoryInterface 
{
    private static $instance;
    
	public function createService(ServiceLocatorInterface $serviceLocator) 
	{
	    if(is_null(self::$instance)) 
	    {
			$entityManager = $serviceLocator->get('doctrine.entitymanager.orm_default');
	    	$e = new EventSourcingUserService($entityManager);
	    	self::$instance = $e;        
        }
	    return self::$instance;
	}
}
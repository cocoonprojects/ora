<?php

namespace User\Service;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

use Ora\User\UserService;

class UserServiceFactory implements FactoryInterface 
{    
	public function createService(ServiceLocatorInterface $serviceLocator)
	{
        $entityManager = $serviceLocator->get('doctrine.entitymanager.orm_default');
        $userService = new UserService($entityManager);
                  
	    return $userService;
	}
}
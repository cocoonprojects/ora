<?php

namespace Application\Service;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Authentication\AuthenticationService;
use ZendExtension\Authentication\MockAuthenticationService;
use Ora\User\User;
use Rhumsaa\Uuid\Uuid;

class AuthenticationServiceFactory implements FactoryInterface 
{
	private static $instance;
    
	public function createService(ServiceLocatorInterface $serviceLocator) 
	{
		if(is_null(self::$instance)) {
 			self::$instance = new AuthenticationService();
			//$userService = $serviceLocator->get('User\UserService');
			//$user = $userService->findUser('60000000-0000-0000-0000-000000000000');
			//self::$instance = new MockAuthenticationService($user);				
		}
	    return self::$instance;
	}
}
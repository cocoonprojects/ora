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
// 			self::$instance = new AuthenticationService();
			$userService = $serviceLocator->get('User\UserService');
			$user = $userService->findUserByEmail('dottorbabba@gmail.com');
			self::$instance = new MockAuthenticationService($user);				
		}
	    return self::$instance;
	}
}
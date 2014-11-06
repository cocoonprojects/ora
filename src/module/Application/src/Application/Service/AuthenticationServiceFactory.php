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
    
	public function createService(ServiceLocatorInterface $serviceLocator) 
	{
//		$authenticationService = new AuthenticationService();
		$user = User::create(Uuid::fromString('60000000-0000-0000-0000-000000000000'));
		$user->setFirstname('Mark');
		$user->setLastname('Rogers');
		$user->setEmail('mark.rogers@ora.local');
		$authenticationService = new MockAuthenticationService($user);
	    return $authenticationService;
	}
}
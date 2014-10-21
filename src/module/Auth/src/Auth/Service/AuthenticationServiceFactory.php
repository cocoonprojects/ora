<?php

namespace Auth\Service;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class AuthenticationServiceFactory implements FactoryInterface 
{
    
	public function createService(ServiceLocatorInterface $serviceLocator) 
	{
		$authenticationService = new \Zend\Authentication\AuthenticationService();
	    return $authenticationService;
	}
}
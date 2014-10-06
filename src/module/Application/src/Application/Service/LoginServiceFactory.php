<?php

namespace Application\Service;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class LoginServiceFactory implements FactoryInterface 
{
	public function createService(ServiceLocatorInterface $serviceLocator) 
	{
		return new LoginService();
	}
}
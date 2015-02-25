<?php

namespace TaskManagement\Service;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class ExecuteTaskAssertionFactory implements FactoryInterface {
		
	
	public function createService(ServiceLocatorInterface $serviceLocator){
		
		$authService = $serviceLocator->get('Zend\Authentication\AuthenticationService');
		$loggedUser = $authService->getIdentity()['user'];
				
		return new ExecuteTaskAssertion($loggedUser);	
		
	}
}




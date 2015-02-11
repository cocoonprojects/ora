<?php

namespace TaskManagement\Service;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class EstimateTaskAssertionFactory implements FactoryInterface {
		
	
	public function createService(ServiceLocatorInterface $serviceLocator){
		
		$authService = $serviceLocator->get('Zend\Authentication\AuthenticationService');
		$loggedUser = $authService->getIdentity()['user'];
			
		return new EstimateTaskAssertion($loggedUser);
		
	}
}




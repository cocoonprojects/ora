<?php

namespace TaskManagement\Service;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class CreateTaskAssertionFactory implements FactoryInterface {
		
	/**
     * @var CreateTaskAssertion
     */
    private static $instance;
	
	public function createService(ServiceLocatorInterface $serviceLocator){
		
		$authService = $serviceLocator->get('Zend\Authentication\AuthenticationService');
		$organizationService = $serviceLocator->get('User\OrganizationService');
	        
		$loggedUser = $authService->getIdentity()['user'];
		$organizationMemberships = $organizationService->findUserOrganizationMembership($loggedUser);
	    
		return new CreateTaskAssertion($organizationMemberships, $loggedUser);		
	}
}




<?php

namespace TaskManagement\Service;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class JoinTaskAssertionFactory implements FactoryInterface {
		
	
	public function createService(ServiceLocatorInterface $serviceLocator){
		
		$authService = $serviceLocator->get('Zend\Authentication\AuthenticationService');
		$organizationService = $serviceLocator->get('User\OrganizationService');
		$taskService = $serviceLocator->get('TaskManagement\TaskService');
        
		$loggedUser = $authService->getIdentity()['user'];
		$organizationMemberships = $organizationService->findUserOrganizationMembership($loggedUser);
        
		$params = $serviceLocator->get('ControllerPluginManager')->get('params')->fromRoute();
		
//		$taskId = $params['id'];
//		$task = $taskService->findTask($params['id']);
//		
//		var_dump(get_class($task));die();
		
		return new JoinTaskAssertion($organizationMemberships, $loggedUser);	
		
	}
}




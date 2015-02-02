<?php

namespace TaskManagement\Service;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

use Ora\TaskManagement\EventSourcingTaskService;
use Ora\TaskManagement\TaskService;

class TaskServiceFactory implements FactoryInterface 
{
    /**
     * @var TaskService
     */
    private static $instance;
    
	public function createService(ServiceLocatorInterface $serviceLocator) 
	{
	    if(is_null(self::$instance)) 
	    {
			$eventStore = $serviceLocator->get('prooph.event_store');
			$entityManager = $serviceLocator->get('doctrine.entitymanager.orm_default');
			$authorize = $serviceLocator->get('BjyAuthorize\Service\Authorize');
			$organizationService = $serviceLocator->get('User\OrganizationService');
			
			self::$instance = new EventSourcingTaskService($eventStore, $entityManager, $authorize, $organizationService);            
        }
	    return self::$instance;
	}
}
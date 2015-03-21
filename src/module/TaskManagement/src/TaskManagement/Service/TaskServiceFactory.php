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
            $service = new EventSourcingTaskService($eventStore, $entityManager);
            self::$instance = $service;
        }
	    return self::$instance;
	}
}
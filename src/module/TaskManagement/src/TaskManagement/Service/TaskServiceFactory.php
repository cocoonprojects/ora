<?php

namespace TaskManagement\Service;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

use Ora\TaskManagement\EventSourcingTaskService;
use Ora\EntitySerializer;

class TaskServiceFactory implements FactoryInterface 
{
    /**
     * @var EventSourcingTaskService
     */
    private static $instance;
    
	public function createService(ServiceLocatorInterface $serviceLocator) 
	{
	    if(is_null(self::$instance)) 
	    {
            $eventStore = $serviceLocator->get('Application\Service\EventStore');

            $entityManager = $serviceLocator->get('doctrine.entitymanager.orm_default');
            $entitySerializer = new EntitySerializer($entityManager);
            
            self::$instance = new EventSourcingTaskService($entityManager, $eventStore, $entitySerializer);            
        }

	    return self::$instance;
	}
}
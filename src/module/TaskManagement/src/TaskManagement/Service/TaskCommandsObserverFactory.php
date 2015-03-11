<?php

namespace TaskManagement\Service;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class TaskCommandsObserverFactory implements FactoryInterface 
{
    /**
     * @var TaskCommandsObserver
     */
    private static $instance;
    
	public function createService(ServiceLocatorInterface $serviceLocator) 
	{
	    if(is_null(self::$instance)) 
	    {
			$entityManager = $serviceLocator->get('doctrine.entitymanager.orm_default');
            $service = new TaskCommandsObserver($entityManager);
            
			$kanbanizeService = $serviceLocator->get('TaskManagement\KanbanizeService');
			$service->setKanbanizeService($kanbanizeService);
			
			$eventStore = $serviceLocator->get('prooph.event_store');
			$service->observe($eventStore);
            self::$instance = $service;
	    }
	    return self::$instance;
	}
}
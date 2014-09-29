<?php

namespace TaskManagement\ServiceFactory;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

use Ora\TaskManagement\EventSourcingTaskService;
use Ora\EventStore\DoctrineEventStore;

class TaskServiceFactory implements FactoryInterface 
{
    protected $taskService;
    
	public function createService(ServiceLocatorInterface $serviceLocator) 
	{
	    $entityManager = $serviceLocator->get('doctrine.entitymanager.orm_default');
	    
	    if(is_null($this->taskService)) {
	        $this->taskService = new EventSourcingTaskService(DoctrineEventStore::instance($entityManager));
	    }
	    
	    return $this->taskService;
	}
}
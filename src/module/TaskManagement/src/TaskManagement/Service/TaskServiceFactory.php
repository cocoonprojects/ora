<?php

namespace TaskManagement\Service;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

use Ora\TaskManagement\EventSourcingTaskService;
use Ora\EventStore\DoctrineEventStore;
use Ora\EntitySerializer;

class TaskServiceFactory implements FactoryInterface 
{
    protected $taskService;
    protected $entitySerializer;
    
	public function createService(ServiceLocatorInterface $serviceLocator) 
	{
	    $entityManager = $serviceLocator->get('doctrine.entitymanager.orm_default');
	    
	    if(is_null($this->entitySerializer)) {
	       $this->entitySerializer = new EntitySerializer($entityManager);
	    }
	    
	    //TODO: richiamare DoctrineEventStore::instance($entityManager) 
	    // tramite il factory creato da Andrea in Application/Service	    
	    if(is_null($this->taskService)) {
	        $this->taskService = new EventSourcingTaskService(DoctrineEventStore::instance($entityManager), $this->entitySerializer);
	    }
	    
	    return $this->taskService;
	}
}
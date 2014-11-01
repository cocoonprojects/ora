<?php

namespace TaskManagement\Service;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

use Ora\ProjectManagement\MockProjectService;

class ProjectServiceFactory implements FactoryInterface 
{
    /**
     * @var EventSourcingProjectService
     */
    private static $instance;
    
	public function createService(ServiceLocatorInterface $serviceLocator)
	{
	    if(is_null(self::$instance)) 
	    {
// 			$eventStore = $serviceLocator->get('prooph.event_store');
// 			$eventStoreStrategy = $serviceLocator->get('prooph.event_store.single_stream_strategy');
//             self::$instance = new EventSourcingProjectService($eventStore, $eventStoreStrategy);
	    	self::$instance = new MockProjectService();
        }
	    return self::$instance;
	}
}
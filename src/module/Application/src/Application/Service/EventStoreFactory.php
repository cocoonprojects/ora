<?php
namespace Application\Service;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Ora\EventStore\DoctrineEventStore;

class EventStoreFactory implements FactoryInterface {
	
	private static $instance;
	
    public function createService(ServiceLocatorInterface $serviceLocator) {
    	if(is_null(self::$instance)) {
	    	$entityManager = $serviceLocator->get('doctrine.entitymanager.orm_default');
			self::$instance = new DoctrineEventStore($entityManager);
		}
		return self::$instance;
	}

}
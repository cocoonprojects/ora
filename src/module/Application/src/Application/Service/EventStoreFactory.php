<?php
namespace Ora\EventStore;

use Zend\ServiceManager\FactoryInterface;

class EventStoreFactory implements FactoryInterface {
	
	private static $instance;
	
    public function createService(ServiceLocatorInterface $serviceLocator) {
		$em = $sm->get('doctrine.entitymanager.orm_default');
    	if(is_null(self::$instance)) {
			self::$instance = new DoctrineEventStore($em);
		}
		return self::$instance;
	}

}
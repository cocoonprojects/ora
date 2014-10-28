<?php
namespace Accounting\Service;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Ora\Accounting\EventSourcingCreditsAccountsService;

class CreditsAccountsServiceFactory implements FactoryInterface {
	
	/**
	 * 
	 * @var EventSourcingCreditsAccountsService
	 */
	private static $instance;
	
	public function createService(ServiceLocatorInterface $serviceLocator) {
		if(is_null(self::$instance)) {
			$eventStore = $serviceLocator->get('prooph.event_store');
			$eventStoreStrategy = $serviceLocator->get('prooph.event_store.single_stream_strategy');
			self::$instance = new EventSourcingCreditsAccountsService($eventStore, $eventStoreStrategy);
		}
		return self::$instance;
	}
	
}

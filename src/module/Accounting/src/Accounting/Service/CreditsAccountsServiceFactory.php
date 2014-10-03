<?php
namespace Accounting\Service;

use Zend\ServiceManager\FactoryInterface;

class CreditsAccountsServiceFactory implements FactoryInterface {
	
	private static $instance;
	
	public function createService(ServiceLocatorInterface $serviceLocator) {
		$eventStore = $serviceLocator->get('Application\Service\EventStore');
		if(is_null(self::$instance)) {
			self::$instance = new EventSourcingCreditsAccountsService($eventStore);
		}
		return $instance;
	}
	
}

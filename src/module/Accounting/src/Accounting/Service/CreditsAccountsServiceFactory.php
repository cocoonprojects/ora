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
			$eventStore = $serviceLocator->get('Application\Service\EventStore');
			self::$instance = new EventSourcingCreditsAccountsService($eventStore);
		}
		return self::$instance;
	}
	
}

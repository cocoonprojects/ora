<?php
namespace Accounting\Service;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Ora\Accounting\EventSourcingAccountService;
use Ora\Accounting\AccountService;

class AccountServiceFactory implements FactoryInterface {
	
	/**
	 * 
	 * @var AccountService
	 */
	private static $instance;
	
	public function createService(ServiceLocatorInterface $serviceLocator) {
		if(is_null(self::$instance)) {
			$eventStore = $serviceLocator->get('prooph.event_store');
			$entityManager = $serviceLocator->get('doctrine.entitymanager.orm_default');
			$service = new EventSourcingAccountService($eventStore, $entityManager);
			$organizationService = $serviceLocator->get('User\OrganizationService');
			$service->observe($organizationService);
			self::$instance = $service;
		}
		return self::$instance;
	}
	
}

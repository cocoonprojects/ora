<?php
namespace Application\Controller\Plugin;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use ZendExtension\Mvc\Controller\Plugin\EventStoreTransactionPlugin;

class TransactionPluginFactory implements FactoryInterface {
	
	private static $instance;
	
	public function createService(ServiceLocatorInterface $pluginManager) {
		if(is_null(self::$instance)) {
			$serviceLocator = $pluginManager->getServiceLocator();
			$transactionManager = $serviceLocator->get('prooph.event_store');
			self::$instance = new EventStoreTransactionPlugin($transactionManager);
		}
		return self::$instance;
	}
	
}
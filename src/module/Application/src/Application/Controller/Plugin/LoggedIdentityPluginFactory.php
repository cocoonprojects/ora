<?php
namespace Application\Controller\Plugin;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use ZendExtension\Mvc\Controller\Plugin\EventStoreTransactionPlugin;
use ZendExtension\Mvc\Controller\Plugin\LoggedIdentity;

class LoggedIdentityPluginFactory implements FactoryInterface {
	
	private static $instance;
	
	public function createService(ServiceLocatorInterface $pluginManager) {
		if(is_null(self::$instance)) {
			$serviceLocator = $pluginManager->getServiceLocator();
			$userService = $serviceLocator->get('User\UserService');
			$authenticationService = $serviceLocator->get('Zend\Authentication\AuthenticationService');
			self::$instance = new LoggedIdentity($authenticationService, $userService);
		}
		return self::$instance;
	}
	
}
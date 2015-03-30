<?php
namespace User;

use Zend\ModuleManager\Feature\AutoloaderProviderInterface;
use Zend\ModuleManager\Feature\ConfigProviderInterface;
use User\Controller\OrganizationsController;
use User\Service\OrganizationCommandsListener;

class Module implements AutoloaderProviderInterface, ConfigProviderInterface
{	 
	public function getControllerConfig() 
	{
		return array(
			'invokables' => array(
				'User\Controller\Users' => 'User\Controller\UsersController'
			),
			'factories' => array(
				'User\Controller\Organizations' => function ($sm) {
					$locator = $sm->getServiceLocator();
					$orgService = $locator->get('User\OrganizationService');
					$controller = new OrganizationsController($orgService);
					return $controller;
				}
			)
		);
	} 
	
	public function getServiceConfig()
	{
		return array (
			'factories' => array (
				'User\UserService' => 'User\Service\UserServiceFactory',
				'User\OrganizationService' => 'User\Service\OrganizationServiceFactory',
				'User\OrganizationCommandsListener' => function ($serviceLocator) {
					$entityManager = $serviceLocator->get('doctrine.entitymanager.orm_default');
					return new OrganizationCommandsListener($entityManager);
				},
			),
		);
	}
		
	public function getConfig()
	{
		return include __DIR__ . '/config/module.config.php';
	}

	public function getAutoloaderConfig()
	{
		return array(
				'Zend\Loader\ClassMapAutoloader' => array(
						__DIR__ . '/autoload_classmap.php',
				),
				'Zend\Loader\StandardAutoloader' => array(
						'namespaces' => array(
								__NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
						)
				)
		);
	}
}
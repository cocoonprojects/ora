<?php
namespace People;

use People\Controller\OrganizationsController;
use People\Controller\MembersController;

class Module
{
	public function getControllerConfig() 
	{
		return array(
			'invokables' => array(
			),
			'factories' => array(
				'People\Controller\Organizations' => function ($sm) {
					$locator = $sm->getServiceLocator();
					$orgService = $locator->get('Application\OrganizationService');
					$controller = new OrganizationsController($orgService);
					return $controller;
				},
				'People\Controller\Members' => function ($sm) {
					$locator = $sm->getServiceLocator();
					$orgService = $locator->get('Application\OrganizationService');
					$controller = new MembersController($orgService);
					return $controller;
				},
			)
		);
	}
	
	public function getServiceConfig()
	{
		return array(
			'invokables' => array(
			),
			'factories' => array(
// 				'Application\OrganizationService' => function ($serviceLocator) {
// 					$eventStore = $serviceLocator->get('prooph.event_store');
// 					$entityManager = $serviceLocator->get('doctrine.entitymanager.orm_default');
// 					return new EventSourcingOrganizationService($eventStore, $entityManager);
// 				},
// 				'Application\OrganizationCommandsListener' => function ($serviceLocator) {
// 					$entityManager = $serviceLocator->get('doctrine.entitymanager.orm_default');
// 					return new OrganizationCommandsListener($entityManager);
// 				},
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
			'Zend\Loader\StandardAutoloader' => array(
				'namespaces' => array(
					__NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
				),
			),
		);
	}
}
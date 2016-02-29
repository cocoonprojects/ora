<?php
namespace FlowManagement;

use FlowManagement\Controller\CardsController;
use Zend\ModuleManager\Feature\AutoloaderProviderInterface;
use Zend\ModuleManager\Feature\ConfigProviderInterface;
use FlowManagement\Service\EventSourcingFlowService;
use FlowManagement\Service\CardCommandsListener;
use FlowManagement\Service\ItemCommandsListener;


class Module implements AutoloaderProviderInterface, ConfigProviderInterface{

	public function getControllerConfig(){
		return array(
			'invokables' => array(
			),
			'factories' => array(
				'FlowManagement\Controller\Cards' => function ($sm) {
					$locator = $sm->getServiceLocator();
					$flowService = $locator->get('FlowManagement\FlowService');
					$controller = new CardsController($flowService);
					return $controller;
				},
			)
		);
	}

	public function getServiceConfig(){
		return [
				'factories' => [
						'FlowManagement\FlowService' => function ($locator) {
							$eventStore = $locator->get('prooph.event_store');
							$entityManager = $locator->get('doctrine.entitymanager.orm_default');
							return new EventSourcingFlowService($eventStore, $entityManager);
						},
						'FlowManagement\CardCommandsListener' => function ($locator) {
							$entityManager = $locator->get('doctrine.entitymanager.orm_default');
							return new CardCommandsListener($entityManager);
						},
						'FlowManagement\ItemCommandsListener' => function ($locator) {
							$flowService = $locator->get('FlowManagement\FlowService');
							$organizationService = $locator->get('People\OrganizationService');
							$userService = $locator->get('Application\UserService');
							$transactionManager = $locator->get('prooph.event_store');
							$taskService = $locator->get('TaskManagement\TaskService');
							return new ItemCommandsListener($flowService, $organizationService, $userService, $transactionManager, $taskService);
						},
				],
		];
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
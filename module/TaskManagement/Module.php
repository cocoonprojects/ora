<?php

namespace TaskManagement;

use Zend\ModuleManager\Feature\AutoloaderProviderInterface;
use Zend\ModuleManager\Feature\ConfigProviderInterface;
use TaskManagement\Controller\MembersController;
use TaskManagement\Controller\TasksController;
use TaskManagement\Controller\TransitionsController;
use TaskManagement\Controller\EstimationsController;
use TaskManagement\Controller\SharesController;
use TaskManagement\Controller\StreamsController;
use TaskManagement\Service\TransferTaskSharesCreditsListener;
use TaskManagement\Service\StreamCommandsListener;
use TaskManagement\Service\TaskCommandsListener;
use TaskManagement\Service\EventSourcingStreamService;
use TaskManagement\Service\EventSourcingTaskService;

class Module implements AutoloaderProviderInterface, ConfigProviderInterface
{		
	public function getControllerConfig() 

	{
		return array(
			'invokables' => array(
				'TaskManagement\Controller\Index' => 'TaskManagement\Controller\IndexController',
			),
			'factories' => array(
				'TaskManagement\Controller\Tasks' => function ($sm) {
					$locator = $sm->getServiceLocator();
					$taskService = $locator->get('TaskManagement\TaskService');
					$streamService = $locator->get('TaskManagement\StreamService');
					$acl = $locator->get('Application\Service\Acl');
					$controller = new TasksController($taskService, $streamService, $acl);
					$accountService = $locator->get('Accounting\CreditsAccountsService');
					$controller->setAccountService($accountService);
					return $controller;
				},
				'TaskManagement\Controller\Members' => function ($sm) {
					$locator = $sm->getServiceLocator();
					$taskService = $locator->get('TaskManagement\TaskService');
					$accountService = $locator->get('Accounting\CreditsAccountsService');
					$controller = new MembersController($taskService);
					$controller->setAccountService($accountService);
					return $controller;
				},
				'TaskManagement\Controller\Transitions' => function ($sm) {
					$locator = $sm->getServiceLocator();
					$taskService = $locator->get('TaskManagement\TaskService');
					$controller = new TransitionsController($taskService);
					return $controller;
				},
				'TaskManagement\Controller\Estimations' => function ($sm) {
					$locator = $sm->getServiceLocator();
					$taskService = $locator->get('TaskManagement\TaskService');					
					$controller = new EstimationsController($taskService);
					return $controller;
				},
				'TaskManagement\Controller\Shares' => function ($sm) {
					$locator = $sm->getServiceLocator();
					$taskService = $locator->get('TaskManagement\TaskService');
					$controller = new SharesController($taskService);
					return $controller;
				},
				'TaskManagement\Controller\Streams' => function ($sm) {
					$locator = $sm->getServiceLocator();
					$streamService = $locator->get('TaskManagement\StreamService');
					$organizationService = $locator->get('People\OrganizationService');
					$controller = new StreamsController($streamService, $organizationService);
					return $controller;
			  	}
			)
		);		
	} 
	
	public function getServiceConfig()
	{
		return array (
			'invokables' => array(
				'TaskManagement\CloseTaskListener' => 'TaskManagement\Service\CloseTaskListener',
			),
			'factories' => array (
				'TaskManagement\StreamService' => function ($locator) {
					$eventStore = $locator->get('prooph.event_store');
					$entityManager = $locator->get('doctrine.entitymanager.orm_default');
					return new EventSourcingStreamService($eventStore, $entityManager);
				},
				'TaskManagement\TaskService' => function ($locator) {
					$eventStore = $locator->get('prooph.event_store');
					$entityManager = $locator->get('doctrine.entitymanager.orm_default');
					return new EventSourcingTaskService($eventStore, $entityManager);
				},					
				'TaskManagement\TaskCommandsListener' => function ($locator) {
					$entityManager = $locator->get('doctrine.entitymanager.orm_default');
					return new TaskCommandsListener($entityManager);
				},
				'TaskManagement\StreamCommandsListener' => function ($locator) {
					$entityManager = $locator->get('doctrine.entitymanager.orm_default');
					return new StreamCommandsListener($entityManager);
				},
				'TaskManagement\TransferTaskSharesCreditsListener' => function ($locator) {
					$taskService = $locator->get('TaskManagement\TaskService');					
					$streamService = $locator->get('TaskManagement\StreamService');
					$organizationService = $locator->get('People\OrganizationService');
					$accountService = $locator->get('Accounting\CreditsAccountsService');
					return new TransferTaskSharesCreditsListener($taskService, $streamService, $organizationService, $accountService);
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

<?php
namespace Kanbanize;

use Zend\ModuleManager\Feature\AutoloaderProviderInterface;
use Zend\ModuleManager\Feature\ConfigProviderInterface;
use Kanbanize\Service\KanbanizeAPI;
use Kanbanize\Service\KanbanizeServiceImpl;
use Kanbanize\Service\SyncTaskListener;
use Kanbanize\Controller\ImportsController;
use Kanbanize\Service\ImportDirector;
use Kanbanize\Service\KanbanizeTasksListener;

class Module implements AutoloaderProviderInterface, ConfigProviderInterface
{
	public function getControllerConfig() 
	{
		return array(
			'invokables' => array(
			),
			'factories' => array(
				'Kanbanize\Controller\Imports' => function($sm){
					$locator = $sm->getServiceLocator();
					$config = $locator->get('Config');
					$organizationService = $locator->get('People\OrganizationService');
					$importDirector = $locator->get('Kanbanize\ImportDirector');
					$notificationService = $locator->get('TaskManagement\NotifyMailListener');
					$controller = new ImportsController($organizationService, $importDirector, $notificationService);
					return $controller;
				}
			)
		);
	}
	
	public function getServiceConfig()
	{
		return array (
			'invokables' => array(
			),
			'factories' => array (
				'Kanbanize\KanbanizeService' => function ($locator) {
					$config = $locator->get('Config');
					$entityManager = $locator->get('doctrine.entitymanager.orm_default');
					return new KanbanizeServiceImpl($entityManager);
				},
				'Kanbanize\SyncTaskListener' => function ($locator) {
					$kanbanizeService = $locator->get('Kanbanize\KanbanizeService');
					$taskService = $locator->get('TaskManagement\TaskService');
					return new SyncTaskListener($kanbanizeService, $taskService);
				},
				'Kanbanize\KanbanizeTasksListener' => function ($locator) {
					$entityManager = $locator->get('doctrine.entitymanager.orm_default');
					$taskService = $locator->get('TaskManagement\TaskService');
					return new KanbanizeTasksListener($taskService, $entityManager);
				},
				'Kanbanize\ImportDirector' => function ($locator) {
					$config = $locator->get('Config');
					$apiKey	= $config['kanbanize']['apikey'];
					$taskService = $locator->get('TaskManagement\TaskService');
					$streamService = $locator->get('TaskManagement\StreamService');
					$userService = $locator->get('Application\UserService');
					$kanbanizeService = $locator->get('Kanbanize\KanbanizeService');
					$transactionManager = $locator->get('prooph.event_store');
					$organizationService = $locator->get('People\OrganizationService');
					$service = new ImportDirector($kanbanizeService, $taskService, $streamService, $transactionManager, $userService, $organizationService);
					$service->setApiKey($apiKey);
					return $service;
				},
			),
			'initializers' => array(
			)
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
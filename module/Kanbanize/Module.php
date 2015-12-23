<?php
namespace Kanbanize;

use Zend\ModuleManager\Feature\AutoloaderProviderInterface;
use Zend\ModuleManager\Feature\ConfigProviderInterface;
use Kanbanize\Controller\ImportsController;
use Kanbanize\Service\KanbanizeAPI;
use Kanbanize\Service\KanbanizeServiceImpl;
use Kanbanize\Service\SyncTaskListener;
use Kanbanize\Service\ImportDirector;
use Kanbanize\Service\ImportTasksListener;
use Kanbanize\Service\TaskCommandsListener;
use Kanbanize\Service\StreamCommandsListener;
use Kanbanize\Service\MailNotificationService;

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
					$notificationService = $locator->get('Kanbanize\MailNotificationService');
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
				'Kanbanize\ImportTasksListener' => function ($locator) {
					$notificationService = $locator->get('Kanbanize\MailNotificationService');
					$organizationService = $locator->get('People\OrganizationService');
					return new ImportTasksListener($organizationService, $notificationService);
				},
				'Kanbanize\TaskCommandsListener' => function ($locator) {
					$entityManager = $locator->get('doctrine.entitymanager.orm_default');
					$taskService = $locator->get('TaskManagement\TaskService');
					return new TaskCommandsListener($entityManager, $taskService);
				},
				'Kanbanize\StreamCommandsListener' => function ($locator) {
					$entityManager = $locator->get('doctrine.entitymanager.orm_default');
					return new StreamCommandsListener($entityManager);
				},
				'Kanbanize\ImportDirector' => function ($locator) {
					$taskService = $locator->get('TaskManagement\TaskService');
					$streamService = $locator->get('TaskManagement\StreamService');
					$userService = $locator->get('Application\UserService');
					$kanbanizeService = $locator->get('Kanbanize\KanbanizeService');
					$transactionManager = $locator->get('prooph.event_store');
					$service = new ImportDirector($kanbanizeService, $taskService, $streamService, $transactionManager, $userService);
					return $service;
				},
				'Kanbanize\MailNotificationService'=> function ($locator){
					$mailService = $locator->get('AcMailer\Service\MailService');
					$orgService = $locator->get('People\OrganizationService');
					$rv = new MailNotificationService($mailService, $orgService);
					return $rv;
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
<?php
namespace Kanbanize;

use Zend\ModuleManager\Feature\AutoloaderProviderInterface;
use Zend\ModuleManager\Feature\ConfigProviderInterface;
use Kanbanize\Controller\BoardsController;
use Kanbanize\Controller\ImportsController;
use Kanbanize\Controller\SettingsController;
use Kanbanize\Service\KanbanizeAPI;
use Kanbanize\Service\KanbanizeServiceImpl;
use Kanbanize\Service\SyncTaskListener;
use Kanbanize\Service\ImportTasksListener;
use Kanbanize\Service\TaskCommandsListener;
use Kanbanize\Service\StreamCommandsListener;
use Kanbanize\Service\MailNotificationService;
use Kanbanize\Service\Kanbanize\Service;

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
					$client = $locator->get('Kanbanize\KanbanizeAPI');
					$kanbanizeService = $locator->get('Kanbanize\KanbanizeService');
					$taskService = $locator->get('TaskManagement\TaskService');
					$userService = $locator->get('Application\UserService');
					$streamService = $locator->get('TaskManagement\StreamService');
					$controller = new ImportsController($organizationService, $client, $kanbanizeService, $taskService, $userService, $streamService);
					if(array_key_exists('assignment_of_shares_timebox', $locator->get('Config'))){
						$assignmentOfSharesTimebox = $locator->get('Config')['assignment_of_shares_timebox'];
						$controller->setIntervalForAssignShares($assignmentOfSharesTimebox);
					}
					return $controller;
				},
				'Kanbanize\Controller\Settings' => function($sm){
					$locator = $sm->getServiceLocator();
					$organizationService = $locator->get('People\OrganizationService');
					$client = $locator->get('Kanbanize\KanbanizeAPI');
					$controller = new SettingsController($organizationService, $client);
					return $controller;
				},
				'Kanbanize\Controller\Boards' => function($sm){
					$locator = $sm->getServiceLocator();
					$organizationService = $locator->get('People\OrganizationService');
					$streamService = $locator->get('TaskManagement\StreamService');
					$client = $locator->get('Kanbanize\KanbanizeAPI');
					$kanbanizeService = $locator->get('Kanbanize\KanbanizeService');
					$controller = new BoardsController($organizationService, $streamService, $client, $kanbanizeService);
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
					$api = $locator->get('Kanbanize\KanbanizeAPI');
					return new KanbanizeServiceImpl($entityManager, $api);
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
				'Kanbanize\KanbanizeAPI' => function ($locator) {
					return new KanbanizeAPI();
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
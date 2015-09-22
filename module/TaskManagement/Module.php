<?php

namespace TaskManagement;

use AcMailer\Service\MailService;
use AcMailer\View\DefaultLayout;
use TaskManagement\Service\CloseTaskListener;
use Zend\ModuleManager\Feature\AutoloaderProviderInterface;
use Zend\ModuleManager\Feature\ConfigProviderInterface;
use TaskManagement\Controller\MembersController;
use TaskManagement\Controller\TasksController;
use TaskManagement\Controller\TransitionsController;
use TaskManagement\Controller\EstimationsController;
use TaskManagement\Controller\SharesController;
use TaskManagement\Controller\StreamsController;
use TaskManagement\Service\NotifyMailListener;
use TaskManagement\Service\TransferTaskSharesCreditsListener;
use TaskManagement\Service\StreamCommandsListener;
use TaskManagement\Service\TaskCommandsListener;
use TaskManagement\Service\EventSourcingStreamService;
use TaskManagement\Service\EventSourcingTaskService;
use TaskManagement\Controller\RemindersController;
use TaskManagement\Controller\MailController;

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
					$organizationService = $locator->get('People\OrganizationService');
					$controller = new TasksController($taskService, $streamService, $acl, $organizationService);
					if(array_key_exists('assignment_of_shares_timebox', $locator->get('Config'))){
						$assignmentOfSharesTimebox = $locator->get('Config')['assignment_of_shares_timebox'];
						$controller->setIntervalForCloseTasks($assignmentOfSharesTimebox);
					}
					return $controller;
				},
				'TaskManagement\Controller\Members' => function ($sm) {
					$locator = $sm->getServiceLocator();
					$taskService = $locator->get('TaskManagement\TaskService');
					$controller = new MembersController($taskService);
					return $controller;
				},
				'TaskManagement\Controller\Transitions' => function ($sm) {
					$locator = $sm->getServiceLocator();
					$taskService = $locator->get('TaskManagement\TaskService');
					$controller = new TransitionsController($taskService);
					if(array_key_exists('assignment_of_shares_timebox', $locator->get('Config'))){
						$assignmentOfSharesTimebox = $locator->get('Config')['assignment_of_shares_timebox'];
						$controller->setIntervalForCloseTasks($assignmentOfSharesTimebox);
					}
					return $controller;
				},
				'TaskManagement\Controller\Estimations' => function ($sm) {
					$locator = $sm->getServiceLocator();
					$organizationService = $locator->get('People\OrganizationService');
					$taskService = $locator->get('TaskManagement\TaskService');
					$acl = $locator->get('Application\Service\Acl');
					$controller = new EstimationsController($organizationService, $taskService, $acl);
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
				},
				'TaskManagement\Controller\Reminders' => function ($sm) {
					$locator = $sm->getServiceLocator();
					$notifyMailListener = $locator->get('TaskManagement\NotifyMailListener');
					$taskService = $locator->get('TaskManagement\TaskService');	
					$controller = new RemindersController($notifyMailListener, $taskService);
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
				'TaskManagement\StreamService' => function ($locator) {
					$eventStore = $locator->get('prooph.event_store');
					$entityManager = $locator->get('doctrine.entitymanager.orm_default');
					return new EventSourcingStreamService($eventStore, $entityManager);
				},
				'TaskManagement\NotifyMailListener'=> function ($locator){
					$mailService = $locator->get('AcMailer\Service\MailService');
					$userService = $locator->get('Application\UserService');
					$taskService = $locator->get('TaskManagement\TaskService');
					$rv = new NotifyMailListener($mailService, $userService, $taskService);
					return $rv;
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
					$transactionManager = $locator->get('prooph.event_store');
					$organizationService = $locator->get('People\OrganizationService');
					$accountService = $locator->get('Accounting\CreditsAccountsService');
					$userService = $locator->get('Application\UserService');
					return new TransferTaskSharesCreditsListener($taskService, $organizationService, $accountService, $userService, $transactionManager);
				},
				'TaskManagement\CloseTaskListener' => function ($locator) {
					$taskService = $locator->get('TaskManagement\TaskService');
					$userService = $locator->get('Application\UserService');
					$transactionManager = $locator->get('prooph.event_store');
					return new CloseTaskListener($taskService, $userService, $transactionManager);
				}
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

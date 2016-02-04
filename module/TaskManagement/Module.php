<?php

namespace TaskManagement;

use TaskManagement\Controller\ApprovalsController;
use TaskManagement\Controller\EstimationsController;
use TaskManagement\Controller\MembersController;
use TaskManagement\Controller\MemberStatsController;
use TaskManagement\Controller\RemindersController;
use TaskManagement\Controller\SharesController;
use TaskManagement\Controller\StreamsController;
use TaskManagement\Controller\TasksController;
use TaskManagement\Controller\TransitionsController;
use TaskManagement\Controller\VotingResultsController;
use TaskManagement\Service\AssignCreditsListener;
use TaskManagement\Service\CloseTaskListener;
use TaskManagement\Service\EventSourcingStreamService;
use TaskManagement\Service\EventSourcingTaskService;
use TaskManagement\Service\NotifyMailListener;
use TaskManagement\Service\StreamCommandsListener;
use TaskManagement\Service\TaskCommandsListener;
use TaskManagement\Service\TransferCreditsListener;
use Zend\ModuleManager\Feature\AutoloaderProviderInterface;
use Zend\ModuleManager\Feature\ConfigProviderInterface;

class Module implements AutoloaderProviderInterface, ConfigProviderInterface
{		
	public function getControllerConfig() 
	{
		return [
			'factories' => [
				'TaskManagement\Controller\Tasks' => function ($sm) {
					$locator = $sm->getServiceLocator();
					$taskService = $locator->get('TaskManagement\TaskService');
					$streamService = $locator->get('TaskManagement\StreamService');
					$organizationService = $locator->get('People\OrganizationService');
					$controller = new TasksController($taskService, $streamService, $organizationService);
					if(array_key_exists('default_tasks_limit', $locator->get('Config'))){
						$size = $locator->get('Config')['default_tasks_limit'];
						$controller->setListLimit($size);
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
						$controller->setIntervalForAssignShares($assignmentOfSharesTimebox);
					}
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
				},
				'TaskManagement\Controller\Reminders' => function ($sm) {
					$locator = $sm->getServiceLocator();
					$notificationService = $locator->get('TaskManagement\NotifyMailListener');
					$taskService = $locator->get('TaskManagement\TaskService');
					$controller = new RemindersController($notificationService, $taskService);
					if(isset($locator->get('Config')['assignment_of_shares_remind_interval'])){
						$assignmentOfSharesRemindInterval = $locator->get('Config')['assignment_of_shares_remind_interval'];
						$controller->setIntervalForRemindAssignmentOfShares($assignmentOfSharesRemindInterval);
					}
					return $controller;
				},
				'TaskManagement\Controller\MemberStats' => function ($sm) {
					$locator = $sm->getServiceLocator();
					$orgService = $locator->get('People\OrganizationService');
					$userService = $locator->get('Application\UserService');
					$taskService = $locator->get('TaskManagement\TaskService');
					$controller = new MemberStatsController($orgService, $taskService, $userService);
					return $controller;
				},
				'TaskManagement\Controller\VotingResults' => function ($sm) {
					$locator = $sm->getServiceLocator();
					$taskService = $locator->get('TaskManagement\TaskService');
					$controller = new VotingResultsController($taskService);
					if(isset($locator->get('Config')['item_idea_voting_timebox'])){
						$itemIdeaVotingTimebox = $locator->get('Config')['item_idea_voting_timebox'];
						$controller->setTimeboxForItemIdeaVoting($itemIdeaVotingTimebox);
					}
					return $controller;
				},
				'TaskManagement\Controller\Approvals' => function ($sm) {
					$locator = $sm->getServiceLocator();
					$taskService = $locator->get('TaskManagement\TaskService');
					$controller = new ApprovalsController($taskService);
					if(isset($locator->get('Config')['item_idea_voting_timebox'])){
						$itemIdeaVotingTimebox = $locator->get('Config')['item_idea_voting_timebox'];
						$controller->setTimeboxForItemIdeaVoting($itemIdeaVotingTimebox);
					}
					return $controller;
				},
			]
		];
	} 
	
	public function getServiceConfig()
	{
		return [
			'factories' => [
				'TaskManagement\StreamService' => function ($locator) {
					$eventStore = $locator->get('prooph.event_store');
					$entityManager = $locator->get('doctrine.entitymanager.orm_default');
					return new EventSourcingStreamService($eventStore, $entityManager);
				},
				'TaskManagement\NotifyMailListener'=> function ($locator){
					$mailService = $locator->get('AcMailer\Service\MailService');
					$userService = $locator->get('Application\UserService');
					$taskService = $locator->get('TaskManagement\TaskService');
					$orgService = $locator->get('People\OrganizationService');
					$rv = new NotifyMailListener($mailService, $userService, $taskService, $orgService);
					$config = $locator->get('Config');
					if(isset($config['mail_domain'])) {
						$rv->setHost($config['mail_domain']);
					}
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
				'TaskManagement\TransferCreditsListener' => function ($locator) {
					$taskService = $locator->get('TaskManagement\TaskService');
					$transactionManager = $locator->get('prooph.event_store');
					$organizationService = $locator->get('People\OrganizationService');
					$accountService = $locator->get('Accounting\CreditsAccountsService');
					$userService = $locator->get('Application\UserService');
					return new TransferCreditsListener($taskService, $organizationService, $accountService, $userService, $transactionManager);
				},
				'TaskManagement\CloseTaskListener' => function ($locator) {
					$taskService = $locator->get('TaskManagement\TaskService');
					$userService = $locator->get('Application\UserService');
					$transactionManager = $locator->get('prooph.event_store');
					return new CloseTaskListener($taskService, $userService, $transactionManager);
				},
				'TaskManagement\CloseItemIdeaListener' => function ($locator) {
					$taskService = $locator->get('TaskManagement\TaskService');
					$organizationService = $locator->get('People\OrganizationService');
					$userService = $locator->get('Application\UserService');
					$transactionManager = $locator->get('prooph.event_store');
					return new CloseItemIdeaListener($taskService,$userService, $organizationService, $transactionManager);
				},
				'TaskManagement\AssignCreditsListener' => function ($locator) {
					$taskService = $locator->get('TaskManagement\TaskService');
					$userService = $locator->get('Application\UserService');
					$transactionManager = $locator->get('prooph.event_store');
					return new AssignCreditsListener($taskService, $userService, $transactionManager);
				}
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

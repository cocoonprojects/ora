<?php
namespace Accounting;

use Accounting\Controller\AccountsController;
use Accounting\Controller\DepositsController;
use Accounting\Controller\IncomingTransfersController;
use Accounting\Controller\IndexController;
use Accounting\Controller\MembersController;
use Accounting\Controller\OrganizationStatementController;
use Accounting\Controller\OutgoingTransfersController;
use Accounting\Controller\PersonalStatementController;
use Accounting\Controller\StatementsController;
use Accounting\Controller\StatsController;
use Accounting\Controller\WithdrawalsController;
use Accounting\Service\AccountCommandsListener;
use Accounting\Service\CreateOrganizationAccountListener;
use Accounting\Service\CreatePersonalAccountListener;
use Accounting\Service\EventSourcingAccountService;
use Zend\ModuleManager\Feature\AutoloaderProviderInterface;
use Zend\ModuleManager\Feature\ConfigProviderInterface;

class Module implements AutoloaderProviderInterface, ConfigProviderInterface
{
	public function getControllerConfig() 
	{
		return array(
			'invokables' => array(
			),
			'factories' => [
				'Accounting\Controller\Index' => function($sm) {
					$locator = $sm->getServiceLocator();
					$organizationService = $locator->get('People\OrganizationService');
					return new IndexController($organizationService);
				},
				'Accounting\Controller\Accounts' => function ($sm) {
					$locator = $sm->getServiceLocator();
					$userService = $locator->get('Application\UserService');
					$accountService = $locator->get('Accounting\CreditsAccountsService');
					$organizationService = $locator->get('People\OrganizationService');
					$controller = new AccountsController($organizationService, $accountService, $userService);
					return $controller;
				},
				'Accounting\Controller\Members' => function ($sm) {
					$locator = $sm->getServiceLocator();
					$accountService = $locator->get('Accounting\CreditsAccountsService');
					$organizationService = $locator->get('People\OrganizationService');
					$controller = new MembersController($organizationService, $accountService);
					return $controller;
				},
				'Accounting\Controller\PersonalStatement' => function ($sm) {
					$locator = $sm->getServiceLocator();
					$accountService = $locator->get('Accounting\CreditsAccountsService');
					$acl = $locator->get('Application\Service\Acl');
					$organizationService = $locator->get('People\OrganizationService');
					$controller = new PersonalStatementController($accountService, $acl, $organizationService);
					if(array_key_exists('personal_transactions_default_limit', $locator->get('Config'))){
						$size = $locator->get('Config')['personal_transactions_default_limit'];
						$controller->setTransactionsLimit($size);
					}
					return $controller;
				},
				'Accounting\Controller\OrganizationStatement' => function ($sm) {
					$locator = $sm->getServiceLocator();
					$accountService = $locator->get('Accounting\CreditsAccountsService');
					$acl = $locator->get('Application\Service\Acl');
					$organizationService = $locator->get('People\OrganizationService');
					$controller = new OrganizationStatementController($accountService, $acl, $organizationService);
					if(array_key_exists('organization_transactions_default_limit', $locator->get('Config'))){
						$size = $locator->get('Config')['organization_transactions_default_limit'];
						$controller->setTransactionsLimit($size);
					}
					return $controller;
				},
				'Accounting\Controller\Deposits' => function ($sm) {
					$locator = $sm->getServiceLocator();
					$accountService = $locator->get('Accounting\CreditsAccountsService');
					$controller = new DepositsController($accountService);
					return $controller;
				},
				'Accounting\Controller\Withdrawals' => function ($sm) {
					$locator = $sm->getServiceLocator();
					$accountService = $locator->get('Accounting\CreditsAccountsService');
					$controller = new WithdrawalsController($accountService);
					return $controller;
				},
				'Accounting\Controller\IncomingTransfers' => function ($sm) {
					$locator = $sm->getServiceLocator();
					$accountService = $locator->get('Accounting\CreditsAccountsService');
					$userService = $locator->get('Application\UserService');
					$organizationService = $locator->get('People\OrganizationService');
					$controller = new IncomingTransfersController($accountService, $userService, $organizationService);
					return $controller;
				},
				'Accounting\Controller\OutgoingTransfers' => function ($sm) {
					$locator = $sm->getServiceLocator();
					$accountService = $locator->get('Accounting\CreditsAccountsService');
					$userService = $locator->get('Application\UserService');
					$organizationService = $locator->get('People\OrganizationService');
					$controller = new OutgoingTransfersController($accountService, $userService, $organizationService);
					return $controller;
				},
				'Accounting\Controller\Stats' => function ($sm) {
					$locator = $sm->getServiceLocator();
					$orgService = $locator->get('People\OrganizationService');
					$userService = $locator->get('Application\UserService');
					$accountService = $locator->get('Accounting\CreditsAccountsService');
					$controller = new StatsController($orgService, $userService, $accountService);
					return $controller;
				},
			]
		);
	}
	
	public function getServiceConfig()
	{
		return array (
			'factories' => array (
				'Accounting\CreditsAccountsService' => function ($locator) {
					$eventStore = $locator->get('prooph.event_store');
					$entityManager = $locator->get('doctrine.entitymanager.orm_default');
					return new EventSourcingAccountService($eventStore, $entityManager);
				},
				'Accounting\AccountCommandsListener' => function ($locator) {
					$entityManager = $locator->get('doctrine.entitymanager.orm_default');
					return new AccountCommandsListener($entityManager);
				},
				'Accounting\CreateOrganizationAccountListener' => function ($locator) {
					$accountService = $locator->get('Accounting\CreditsAccountsService');
					$organizationService = $locator->get('People\OrganizationService');
					$userService = $locator->get('Application\UserService');
					return new CreateOrganizationAccountListener($accountService, $organizationService, $userService);
				},
				'Accounting\CreatePersonalAccountListener' => function ($locator) {
					$accountService = $locator->get('Accounting\CreditsAccountsService');
					$organizationService = $locator->get('People\OrganizationService');
					$userService = $locator->get('Application\UserService');
					return new CreatePersonalAccountListener($accountService, $userService, $organizationService);
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
<?php
namespace Accounting;

use Zend\ModuleManager\Feature\AutoloaderProviderInterface;
use Zend\ModuleManager\Feature\ConfigProviderInterface;
use Accounting\Controller\IndexController;
use Accounting\Controller\AccountsController;
use Accounting\Controller\DepositsController;
use Accounting\Controller\StatementsController;
use Accounting\Service\CreateOrganizationAccountListener;
use Accounting\Service\CreatePersonalAccountListener;
use Accounting\Service\AccountCommandsListener;
use Accounting\Service\EventSourcingAccountService;

class Module implements AutoloaderProviderInterface, ConfigProviderInterface
{
	public function getControllerConfig() 
	{
		return array(
			'invokables' => array(
				'Accounting\Controller\Index' => 'Accounting\Controller\IndexController',
			),
			'factories' => array(
				'Accounting\Controller\Accounts' => function ($sm) {
					$locator = $sm->getServiceLocator();
					$accountService = $locator->get('Accounting\CreditsAccountsService');
					$acl = $locator->get('Application\Service\Acl');
					$controller = new AccountsController($accountService, $acl);
					return $controller;
				},
				'Accounting\Controller\Deposits' => function ($sm) {
					$locator = $sm->getServiceLocator();
					$accountService = $locator->get('Accounting\CreditsAccountsService');
					$controller = new DepositsController($accountService);
					return $controller;
				},
				'Accounting\Controller\Statement' => function ($sm) {
					$locator = $sm->getServiceLocator();
					$accountService = $locator->get('Accounting\CreditsAccountsService');
					$acl = $locator->get('Application\Service\Acl');
					$controller = new StatementsController($accountService, $acl);
					return $controller;
				},
			)
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
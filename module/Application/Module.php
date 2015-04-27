<?php
namespace Application;

use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;
use Zend\Authentication\AuthenticationService;
use Zend\Permissions\Acl\Acl;
use ZFX\Controller\Plugin\IsAllowed;
use Application\Controller\AuthController;
use Application\Controller\MembershipsController;
use Application\Controller\Plugin\EventStoreTransactionPlugin;
use Application\Service\IdentityRolesProvider;
use Application\Service\OrganizationCommandsListener;
use Application\Service\EventSourcingUserService;
use Application\Service\EventSourcingOrganizationService;

class Module
{
	public function onBootstrap(MvcEvent $e)
	{
		$application = $e->getApplication();
		$eventManager = $application->getEventManager();
		$serviceManager = $application->getServiceManager();
		
		$moduleRouteListener = new ModuleRouteListener();
		$moduleRouteListener->attach($eventManager);		
	}
	
	public function getControllerConfig() 
	{
		return array(
			'invokables' => array(
				'Application\Controller\Index' => 'Application\Controller\IndexController',
			),
			'factories' => array(
				'Application\Controller\Auth'  => function ($sm) {
					$locator = $sm->getServiceLocator();
					$resolver = $locator->get('Application\Service\AdapterResolver');
					$authService = $locator->get('Zend\Authentication\AuthenticationService');
					$userService = $locator->get('Application\UserService');
					$controller = new AuthController($authService, $resolver);
					$controller->setUserService($userService);
					return $controller;
				},
				'Application\Controller\Memberships' => function ($sm) {
					$locator = $sm->getServiceLocator();
					$orgService = $locator->get('Application\OrganizationService');
					$controller = new MembershipsController($orgService);
					return $controller;
				}
			)
		);
	}
	
	public function getControllerPluginConfig()
	{
		return array(
			'factories' => array(
				'transaction' => function ($pluginManager) {
					$serviceLocator = $pluginManager->getServiceLocator();
					$transactionManager = $serviceLocator->get('prooph.event_store');
					return new EventStoreTransactionPlugin($transactionManager);
				},
				'isAllowed' => function ($pluginManager) {
					$serviceLocator = $pluginManager->getServiceLocator();
					$acl = $serviceLocator->get('Application\Service\Acl');
					return new IsAllowed($acl);
				},
			),
		);
	}
	
	public function getServiceConfig()
	{
		return array(
			'invokables' => array(
				'Zend\Authentication\AuthenticationService' => AuthenticationService::class,
			),
			'factories' => array(
				'Application\Service\AdapterResolver' => 'Application\Service\OAuth2AdapterResolverFactory',
				'Application\Service\Acl' => 'Application\Service\AclFactory',
				'Application\Service\IdentityRolesProvider' => function($serviceLocator){
					$authService = $serviceLocator->get('Zend\Authentication\AuthenticationService');
					$provider = new IdentityRolesProvider($authService);
					return $provider;
				},
				'Authorization\CurrentUserProvider' => function($serviceLocator){
					$authService = $serviceLocator->get('Zend\Authentication\AuthenticationService');
					$loggedUser = $authService->getIdentity()['user'];
					return $loggedUser;
				},	
				'Application\OrganizationService' => function ($serviceLocator) {
					$eventStore = $serviceLocator->get('prooph.event_store');
					$entityManager = $serviceLocator->get('doctrine.entitymanager.orm_default');
					return new EventSourcingOrganizationService($eventStore, $entityManager);
				},
				'Application\UserService' => function ($serviceLocator) {
					$entityManager = $serviceLocator->get('doctrine.entitymanager.orm_default');
					return new EventSourcingUserService($entityManager);
				},
				'Application\OrganizationCommandsListener' => function ($serviceLocator) {
					$entityManager = $serviceLocator->get('doctrine.entitymanager.orm_default');
					return new OrganizationCommandsListener($entityManager);
				},
			),
		);
	}
	
	public function getViewHelperConfig()
	{
		return array(
			'invokables' => array(
				'LoginPopupHelper' => 'Application\View\Helper\LoginPopupHelper',
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
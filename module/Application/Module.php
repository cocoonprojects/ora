<?php
namespace Application;

use Application\Authentication\OAuth2\LoadLocalProfileListener;
use Application\Controller\AuthController;
use Application\Controller\IndexController;
use Application\Controller\MembershipsController;
use Application\Service\DomainEventDispatcher;
use Application\Service\EventSourcingUserService;
use Zend\Mvc\Application;
use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;
use Zend\Authentication\AuthenticationService;
use ZFX\EventStore\Controller\Plugin\EventStoreTransactionPlugin;
use ZFX\Acl\Controller\Plugin\IsAllowed;
use ZFX\Authentication\DomainAdapter;


class Module
{
	public function onBootstrap(MvcEvent $e)
	{
		$application = $e->getApplication();
		$eventManager = $application->getEventManager();
		$serviceManager = $application->getServiceManager();
		//prepends the module name to the requested controller name. That's useful if you want to use controller short names in routing
		$moduleRouteListener = new ModuleRouteListener();
		$moduleRouteListener->attach($eventManager);	
		
		$eventManager->attach(MvcEvent::EVENT_DISPATCH_ERROR, function($event) use($serviceManager) {
			$error  = $event->getError();
			if ($error == Application::ERROR_ROUTER_NO_MATCH) {
				$response = $event->getResponse();
				$response->setStatusCode(404);
				$response->send();
			}
		}, 100);
		
 		$eventManager->attach(MvcEvent::EVENT_DISPATCH, function($event) use($serviceManager) {
 			$authService = $serviceManager->get('Zend\Authentication\AuthenticationService');
 			if(!$authService->hasIdentity()){
 				$userService = $serviceManager->get('Application\UserService');
 				$localhostAuthAdapter = new DomainAdapter($_SERVER['HTTP_HOST'], $userService);
 				$authService->authenticate($localhostAuthAdapter);
 			}
 		}, 100);
	}
	
	public function getControllerConfig() 
	{
		return array(
			'invokables' => array(
				'Application\Controller\Index' => 'Application\Controller\IndexController'
			),
			'factories' => array(
				'Application\Controller\Auth'  => function ($sm) {
					$locator = $sm->getServiceLocator();
					$resolver = $locator->get('Application\Service\AdapterResolver');
					$authService = $locator->get('Zend\Authentication\AuthenticationService');
					$controller = new AuthController($authService, $resolver);
					return $controller;
				},
				'Application\Controller\Memberships' => function ($sm) {
					$locator = $sm->getServiceLocator();
					$orgService = $locator->get('People\OrganizationService');
					$controller = new MembershipsController($orgService);
					return $controller;
				},
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
				'Application\DomainEventDispatcher' => DomainEventDispatcher::class
			),
			'factories' => array(
				'Application\Service\AdapterResolver' => 'Application\Service\OAuth2AdapterResolverFactory',
				'Application\Service\Acl' => 'Application\Service\AclFactory',
				'Application\UserService' => function ($serviceLocator) {
					$entityManager = $serviceLocator->get('doctrine.entitymanager.orm_default');
					return new EventSourcingUserService($entityManager);
				},
				'Application\LoadLocalProfileListener' => function($serviceLocator) {
					$userService = $serviceLocator->get('Application\UserService');
					return new LoadLocalProfileListener($userService);
				},
			),
		);
	}
	
	public function getViewHelperConfig()
	{
		return array(
			'invokables' => array(
				'LoginHelper' => 'Application\View\Helper\LoginHelper',
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

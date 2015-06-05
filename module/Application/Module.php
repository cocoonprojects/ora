<?php
namespace Application;

use Application\Authentication\OAuth2\LoadLocalProfileListener;
use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;
use Zend\Authentication\AuthenticationService;
use Zend\Permissions\Acl\Acl;
use Application\Controller\AuthController;
use Application\Controller\MembershipsController;
use Application\Service\EventSourcingUserService;
use ZFX\EventStore\Controller\Plugin\EventStoreTransactionPlugin;
use ZFX\Acl\Controller\Plugin\IsAllowed;
use Application\Entity\User;
use Application\Authentication\DomainBased\DomainBasedAuthentication;
use Application\Authentication\DomainBased\Application\Authentication\DomainBased;

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

		$sharedEventManager = $eventManager->getSharedManager();
		$sharedEventManager->attach('Zend\Stdlib\DispatchableInterface', MvcEvent::EVENT_DISPATCH, function($event) use($serviceManager) {

			$request = $event->getRequest();			
			$acl = $serviceManager->get('Application\Service\Acl');
			if($acl->isAllowed(NULL, NULL, 'Application.Authentication.authenticateFromLocalhost')){				
				$authService = $serviceManager->get('Zend\Authentication\AuthenticationService');
				$localhostAuthAdapter = $serviceManager->get('Application\Service\LocalhostBasedAuthentication');
				$authService->authenticate($localhostAuthAdapter);	
			}
		}, 10);
		
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
					$controller = new AuthController($authService, $resolver);
					return $controller;
				},
				'Application\Controller\Memberships' => function ($sm) {
					$locator = $sm->getServiceLocator();
					$orgService = $locator->get('People\OrganizationService');
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
				'Application\UserService' => function ($serviceLocator) {
					$entityManager = $serviceLocator->get('doctrine.entitymanager.orm_default');
					return new EventSourcingUserService($entityManager);
				},
				'Application\LoadLocalProfileListener' => function($serviceLocator) {
					$userService = $serviceLocator->get('Application\UserService');
					return new LoadLocalProfileListener($userService);
				},
				'Application\Service\LocalhostBasedAuthentication' => function($serviceLocator){
					$userService = $serviceLocator->get('Application\UserService');
					return new DomainBasedAuthentication('localhost', $userService);

				}
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
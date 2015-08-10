<?php
namespace Application;

use Application\Authentication\OAuth2\LoadLocalProfileListener;
use Application\Controller\AuthController;
use Application\Controller\IndexController;
use Application\Controller\MembershipsController;
use Application\Service\DomainEventDispatcher;
use Application\Service\EventSourcingUserService;
<<<<<<< HEAD
use Zend\Mvc\Application;
=======
use Zend\Authentication\AuthenticationService;
use Zend\Authentication\Storage\NonPersistent;
>>>>>>> Set request related authentication based on JWT
use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;
use ZFX\Acl\Controller\Plugin\IsAllowed;
use ZFX\Authentication\GoogleJWTAdapter;
use ZFX\Authentication\JWTAdapter;
use ZFX\Authentication\JWTBuilder;
use ZFX\EventStore\Controller\Plugin\EventStoreTransactionPlugin;


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

		$request = $e->getRequest();
 		$eventManager->attach(MvcEvent::EVENT_DISPATCH, function($event) use($serviceManager, $request) {
			if($token = $request->getHeaders('ORA-JWT')) {
				$builder = $serviceManager->get('Application\JWTBuilder');
				$adapter = new JWTAdapter($builder);
				$adapter->setToken($token->getFieldValue());

				$authService = $serviceManager->get('Zend\Authentication\AuthenticationService');
				$result = $authService->authenticate($adapter);
			} elseif($token = $request->getHeaders('GOOGLE-JWT')) {
				$client = $serviceManager->get('Application\Service\GoogleAPIClient');
				$adapter = new GoogleJWTAdapter($client);
				$adapter->setToken($token->getFieldValue());

				$authService = $serviceManager->get('Zend\Authentication\AuthenticationService');
				$result = $authService->authenticate($adapter);
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
					$builder = $locator->get('Application\JWTBuilder');
					$controller = new AuthController($authService, $resolver, $builder);
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
				'Application\DomainEventDispatcher' => DomainEventDispatcher::class
			),
			'factories' => array(
				'Zend\Authentication\AuthenticationService' => function ($serviceLocator) {
					$rv = new AuthenticationService();
					$rv->setStorage(new NonPersistent());
					return $rv;
				},
				'Application\Service\AdapterResolver' => 'Application\Service\OAuth2AdapterResolverFactory',
				'Application\Service\Acl' => 'Application\Service\AclFactory',
				'Application\UserService' => function ($serviceLocator) {
					$entityManager = $serviceLocator->get('doctrine.entitymanager.orm_default');
					return new EventSourcingUserService($entityManager);
				},
				'Application\LoadLocalProfileListener' => function($serviceLocator) {
					$userService = $serviceLocator->get('Application\UserService');
					$google = $serviceLocator->get('Application\Service\GoogleAPIClient');
					return new LoadLocalProfileListener($userService, $google);
				},
				'Application\Service\GoogleAPIClient' => function ($serviceLocator) {
					$config = $serviceLocator->get('Config');
					if(!isset($config['zendoauth2'])) {
						throw new \Exception('ZendOAuth2 config not found');
					}
					if(!isset($config['zendoauth2']['google'])) {
						throw new \Exception('ZendOAuth2/Google config not found');
					}
					$googleConfig = $config['zendoauth2']['google'];
					$rv = new \Google_Client();
					$rv->setClientId($googleConfig['client_id']);
					$rv->setClientSecret($googleConfig['client_secret']);
					$rv->setRedirectUri($googleConfig['redirect_uri']);
					$rv->setApplicationName('O.R.A. Platform');
					return $rv;
				},
				'Application\JWTBuilder' => function($serviceLocator) {
					$config = $serviceLocator->get('Config');
					if(!isset($config['jwt'])) {
						throw new \Exception('JWT config not found');
					}
					$jwt = $config['jwt'];
					if(!isset($jwt['private-key'])) {
						throw new \Exception('JWT private-key config not found');
					}
					if(!($privateKey = openssl_pkey_get_private($jwt['private-key']))) {
						throw new \Exception('Error loading private key ' . $jwt['private-key'] . ':' . openssl_error_string());
					}
					if(!isset($jwt['public-key'])) {
						throw new \Exception('JWT public-key config not found');
					}
					if(!($publicKey = openssl_pkey_get_public($jwt['public-key']))) {
						throw new \Exception('Error loading public key ' . $jwt['public-key'] . ':' . openssl_error_string());
					}
					$rv = new JWTBuilder($privateKey, $publicKey);
					if(isset($jwt['time-to-live'])) {
						$rv->setTimeToLive($jwt['time-to-live']);
					}
					if(isset($jwt['algorithm'])) {
						$rv->setAlgorithm($jwt['algorithm']);
					}
					return $rv;
				}
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

<?php
namespace Accounting;

use Zend\ModuleManager\Feature\AutoloaderProviderInterface;
use Zend\ModuleManager\Feature\ConfigProviderInterface;
use Zend\Mvc\MvcEvent;
use Accounting\Controller\IndexController;
use Accounting\Service\AccountListener;
use Accounting\Controller\AccountsController;

class Module implements AutoloaderProviderInterface, ConfigProviderInterface
{
	
	public function onBootstrap(MvcEvent $e)
	{
		$application = $e->getApplication();
		$eventManager = $application->getEventManager();
		$serviceManager = $application->getServiceManager();
	
		$entityManager = $serviceManager->get('doctrine.entitymanager.orm_default');
		$eventStore = $serviceManager->get('prooph.event_store');
		$accountListener = new AccountListener($entityManager);
		$accountListener->attach($eventStore);
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
	
    public function getControllerConfig() 
    {
        return array(
            'invokables' => array(
				'Accounting\Controller\Accounts' => 'Accounting\Controller\AccountsController',
            ),
            'factories' => array(
	            'Accounting\Controller\Index' => function ($sm) {
	            	$locator = $sm->getServiceLocator();
	            	$authService = $locator->get('Application\Service\AuthenticationService');
	            	$accountService = $locator->get('Accounting\CreditsAccountsService');
	            	$controller = new IndexController($accountService, $authService);
	            	return $controller;
	            },
	            'Accounting\Controller\Accounts' => function ($sm) {
	            	$locator = $sm->getServiceLocator();
	            	$accountService = $locator->get('Accounting\CreditsAccountsService');
	            	$controller = new AccountsController($accountService);
	            	return $controller;
	            }
            )
        );        
    } 
	
	public function getServiceConfig()
	{
		return array (
			'factories' => array (
				'Accounting\CreditsAccountsService' => 'Accounting\Service\AccountServiceFactory',
     			),
     	);
	}

}

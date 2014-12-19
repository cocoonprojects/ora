<?php
namespace Accounting;

use Zend\ModuleManager\Feature\AutoloaderProviderInterface;
use Zend\ModuleManager\Feature\ConfigProviderInterface;
use Zend\Mvc\MvcEvent;
use Accounting\Controller\IndexController;
use Accounting\Service\AccountListener;
use Accounting\Controller\AccountsController;
use Accounting\Controller\DepositsController;
use Accounting\Controller\StatementsController;

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
	            	$authService = $locator->get('Application\Service\AuthenticationService');
	            	$accountService = $locator->get('Accounting\CreditsAccountsService');
	            	$controller = new AccountsController($accountService, $authService);
	            	return $controller;
	            },
				'Accounting\Controller\Deposits' => function ($sm) {
	            	$locator = $sm->getServiceLocator();
	            	$authService = $locator->get('Application\Service\AuthenticationService');
	            	$accountService = $locator->get('Accounting\CreditsAccountsService');
	            	$controller = new DepositsController($accountService, $authService);
	            	$eventStore = $locator->get('prooph.event_store');
	            	$controller->setTransactionManager($eventStore);
	            	return $controller;
	            },
				'Accounting\Controller\Statements' => function ($sm) {
	            	$locator = $sm->getServiceLocator();
	            	$authService = $locator->get('Application\Service\AuthenticationService');
	            	$accountService = $locator->get('Accounting\CreditsAccountsService');
	            	$controller = new StatementsController($accountService, $authService);
	            	return $controller;
	            },
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

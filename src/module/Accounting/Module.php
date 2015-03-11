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
	
		$serviceManager->get('Accounting\CreditsAccountsService');
		$serviceManager->get('Accounting\AccountCommandsObserver');
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
            		'Accounting\Controller\Index' => 'Accounting\Controller\IndexController',
            ),
            'factories' => array(
	            'Accounting\Controller\Accounts' => function ($sm) {
	            	$locator = $sm->getServiceLocator();
	            	$accountService = $locator->get('Accounting\CreditsAccountsService');
	            	$controller = new AccountsController($accountService);
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
	            	$controller = new StatementsController($accountService);
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
				'Accounting\AccountCommandsObserver' => 'Accounting\Service\AccountCommandsObserverFactory',
			),
     	);
	}

}

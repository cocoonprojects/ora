<?php

namespace User;

use Zend\ModuleManager\Feature\AutoloaderProviderInterface;
use Zend\ModuleManager\Feature\ConfigProviderInterface;
use Zend\Mvc\MvcEvent;
use User\Controller\OrganizationsController;
use User\Service\OrganizationListener;

class Module implements AutoloaderProviderInterface, ConfigProviderInterface
{    
	public function onBootstrap(MvcEvent $e)
	{
		$application = $e->getApplication();
		$eventManager = $application->getEventManager();
		$serviceManager = $application->getServiceManager();
	
		$serviceManager->get('User\OrganizationCommandsObserver');
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
            	'User\Controller\Users' => 'User\Controller\UsersController'
            ),
            'factories' => array(
	            'User\Controller\Organizations' => function ($sm) {
	            	$locator = $sm->getServiceLocator();
	            	$orgService = $locator->get('User\OrganizationService');
	            	$controller = new OrganizationsController($orgService);
	            	return $controller;
	            }
            )
        );        
    } 
    
    public function getServiceConfig()
    {
        return array (
            'factories' => array (
                'User\UserService' => 'User\Service\UserServiceFactory',
                'User\OrganizationService' => 'User\Service\OrganizationServiceFactory',
                'User\OrganizationCommandsObserver' => 'User\Service\OrganizationCommandsObserverFactory'
            ),
        );
    }
}
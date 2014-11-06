<?php

namespace TaskManagement;

use Zend\ModuleManager\Feature\AutoloaderProviderInterface;
use Zend\ModuleManager\Feature\ConfigProviderInterface;
use TaskManagement\Controller\MembersController;
use TaskManagement\Controller\TasksController;

class Module implements AutoloaderProviderInterface, ConfigProviderInterface
{    
	public function onBootstrap(MvcEvent $e)
	{
        $sm = $e->getApplication()->getServiceManager();
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
	            'TaskManagement\Controller\Projects' => 'TaskManagement\Controller\ProjectsController',
            ),
            'factories' => array(
	            'TaskManagement\Controller\Tasks' => function ($sm) {
	            	$locator = $sm->getServiceLocator();
	            	$authService = $locator->get('Application\Service\AuthenticationService');
	            	$taskService = $locator->get('TaskManagement\TaskService');
	            	$controller = new TasksController($taskService, $authService);
	            	return $controller;
	            },
	            'TaskManagement\Controller\Members' => function ($sm) {
            		$locator = $sm->getServiceLocator();
            		$authService = $locator->get('Application\Service\AuthenticationService');
            		$taskService = $locator->get('TaskManagement\TaskService');
            		$controller = new MembersController($taskService, $authService);
            		return $controller;
            	},
            )
        );        
    } 
    
    public function getServiceConfig()
    {
        return array (
            'factories' => array (
                'TaskManagement\ProjectService' => 'TaskManagement\Service\ProjectServiceFactory',
            	'TaskManagement\TaskService' => 'TaskManagement\Service\TaskServiceFactory'
            ),
        );
    }
}
<?php

namespace TaskManagement;

use Zend\ModuleManager\Feature\AutoloaderProviderInterface;
use Zend\ModuleManager\Feature\ConfigProviderInterface;
use TaskManagement\Controller\MembersController;
use TaskManagement\Controller\TasksController;
use Zend\Mvc\MvcEvent;
use Ora\TaskManagement\TaskListener;
use TaskManagement\Controller\TransitionsController;
use TaskManagement\Controller\EstimationController;

class Module implements AutoloaderProviderInterface, ConfigProviderInterface
{    
	public function onBootstrap(MvcEvent $e)
	{
		$application = $e->getApplication();
		$eventManager = $application->getEventManager();
		$serviceManager = $application->getServiceManager();
	
		$entityManager = $serviceManager->get('doctrine.entitymanager.orm_default');
		$eventStore = $serviceManager->get('prooph.event_store');
		$taskListener = new TaskListener($entityManager);
		$taskListener->attach($eventStore);
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
	            'TaskManagement\Controller\Index' => 'TaskManagement\Controller\IndexController',
	            'TaskManagement\Controller\Projects' => 'TaskManagement\Controller\ProjectsController',
            ),
            'factories' => array(
	            'TaskManagement\Controller\Tasks' => function ($sm) {
	            	$locator = $sm->getServiceLocator();
	            	$authService = $locator->get('Application\Service\AuthenticationService');
	            	$taskService = $locator->get('TaskManagement\TaskService');
	            	$projectService = $locator->get('TaskManagement\ProjectService');
	            	$organizationService = $locator->get('User\OrganizationService');
	            	$controller = new TasksController($taskService, $authService, $projectService, $organizationService);
	            	return $controller;
	            },
	            'TaskManagement\Controller\Members' => function ($sm) {
            		$locator = $sm->getServiceLocator();
            		$authService = $locator->get('Application\Service\AuthenticationService');
            		$taskService = $locator->get('TaskManagement\TaskService');
            		$projectService = $locator->get('TaskManagement\ProjectService');
            		$controller = new MembersController($taskService, $authService, $projectService);
            		return $controller;
            	},
            	'TaskManagement\Controller\Transitions' => function ($sm) {
            		$locator = $sm->getServiceLocator();
            		$kanbanizeService = $locator->get('TaskManagement\KanbanizeService');
            		$controller = new TransitionsController($kanbanizeService);
            		return $controller;
            	},
            	'TaskManagement\Controller\Estimation' => function ($sm) {
            		$locator = $sm->getServiceLocator();
            		$authService = $locator->get('Application\Service\AuthenticationService');
            		$taskService = $locator->get('TaskManagement\TaskService');
            		$projectService = $locator->get('TaskManagement\ProjectService');
            		$controller = new EstimationController($taskService, $authService, $projectService);
            		return $controller;
            	}
            )
        );        
    } 
    
    public function getServiceConfig()
    {
        return array (
            'factories' => array (
                'TaskManagement\ProjectService' => 'TaskManagement\Service\ProjectServiceFactory',
            	'TaskManagement\TaskService' => 'TaskManagement\Service\TaskServiceFactory',
				'TaskManagement\KanbanizeService' => 'TaskManagement\Service\KanbanizeServiceFactory',
            ),
        );
    }
}
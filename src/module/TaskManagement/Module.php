<?php

namespace TaskManagement;

use Zend\Mvc\MvcEvent;
use Zend\ModuleManager\Feature\AutoloaderProviderInterface;
use Zend\ModuleManager\Feature\ConfigProviderInterface;
use TaskManagement\Controller\MembersController;
use TaskManagement\Controller\TasksController;
use TaskManagement\Controller\TransitionsController;
use TaskManagement\Controller\EstimationsController;
use TaskManagement\Service\TaskListener;
use TaskManagement\Controller\SharesController;


class Module implements AutoloaderProviderInterface, ConfigProviderInterface
{    
	public function onBootstrap(MvcEvent $e)
	{
		$application = $e->getApplication();
		$eventManager = $application->getEventManager();
		$serviceManager = $application->getServiceManager();
	
		$entityManager = $serviceManager->get('doctrine.entitymanager.orm_default');
		$eventStore = $serviceManager->get('prooph.event_store');
		$kanbanizeService = $serviceManager->get('TaskManagement\KanbanizeService');
		$taskListener = new TaskListener($entityManager);
		$taskListener->setKanbanizeService($kanbanizeService);
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
	            'TaskManagement\Controller\Streams' => 'TaskManagement\Controller\StreamsController',        		
            ),
            'factories' => array(
	            'TaskManagement\Controller\Tasks' => function ($sm) {
	            	$locator = $sm->getServiceLocator();
	            	$taskService = $locator->get('TaskManagement\TaskService');
	            	$streamService = $locator->get('TaskManagement\StreamService');
	            	$authorize = $locator->get('BjyAuthorize\Service\Authorize');
	            	$controller = new TasksController($taskService, $streamService, $authorize);
	            	return $controller;
	            },
	            'TaskManagement\Controller\Members' => function ($sm) {
            		$locator = $sm->getServiceLocator();
            		$taskService = $locator->get('TaskManagement\TaskService');            		
            		$controller = new MembersController($taskService);

            		return $controller;
            	},
            	'TaskManagement\Controller\Transitions' => function ($sm) {
            		$locator = $sm->getServiceLocator();
	            	$taskService = $locator->get('TaskManagement\TaskService');
            		$kanbanizeService = $locator->get('TaskManagement\KanbanizeService');            		
            		$controller = new TransitionsController($taskService, $kanbanizeService);
            		return $controller;
            	},
            	'TaskManagement\Controller\Estimations' => function ($sm) {
            		$locator = $sm->getServiceLocator();
            		$taskService = $locator->get('TaskManagement\TaskService');            		
            		$controller = new EstimationsController($taskService);

            		return $controller;
            	},
            	'TaskManagement\Controller\Shares' => function ($sm) {
            		$locator = $sm->getServiceLocator();
            		$taskService = $locator->get('TaskManagement\TaskService');
            		$controller = new SharesController($taskService);
            		return $controller;
            	}
            )
        );        
    } 
    
    public function getServiceConfig()
    {
        return array (
            'factories' => array (
                'TaskManagement\StreamService' => 'TaskManagement\Service\StreamServiceFactory',
            	'TaskManagement\TaskService' => 'TaskManagement\Service\TaskServiceFactory',
				'TaskManagement\KanbanizeService' => 'TaskManagement\Service\KanbanizeServiceFactory',

			    'MemberOfOrganizationAssertion' =>  function($sl){			        
        			$authService = $sl->get('Zend\Authentication\AuthenticationService');
					$loggedUser = $authService->getIdentity()['user'];							
					return new Service\MemberOfOrganizationAssertion($loggedUser);					
			    },
				'TaskMemberAndOngoingTaskAssertion' =>  function($sl){			        
        			$authService = $sl->get('Zend\Authentication\AuthenticationService');
					$loggedUser = $authService->getIdentity()['user'];							
					return new Service\TaskMemberAndOngoingTaskAssertion($loggedUser);
				},
				'OrganizationMemberNotTaskMemberAndNotCompletedTaskAssertion' =>  function($sl){			        
        			$authService = $sl->get('Zend\Authentication\AuthenticationService');
					$loggedUser = $authService->getIdentity()['user'];							
					return new Service\OrganizationMemberNotTaskMemberAndNotCompletedTaskAssertion($loggedUser);
				},
        		'TaskMemberNotOwnerAndNotCompletedTaskAssertion' =>  function($sl){			        
        			$authService = $sl->get('Zend\Authentication\AuthenticationService');
					$loggedUser = $authService->getIdentity()['user'];							
					return new Service\TaskMemberNotOwnerAndNotCompletedTaskAssertion($loggedUser);
				},
				'TaskOwnerAndNotCompletedTaskAssertion' => function($sl){			        
        			$authService = $sl->get('Zend\Authentication\AuthenticationService');
					$loggedUser = $authService->getIdentity()['user'];							
					return new Service\TaskOwnerAndNotCompletedTaskAssertion($loggedUser);
				},
        		'TaskMemberNotOwnerAndOpenOrCompletedTaskAssertion' =>  function($sl){			        
        			$authService = $sl->get('Zend\Authentication\AuthenticationService');
					$loggedUser = $authService->getIdentity()['user'];							
					return new Service\TaskMemberNotOwnerAndOpenOrCompletedTaskAssertion($loggedUser);
				},
        		'TaskOwnerAndOngoingOrAcceptedTaskAssertion' =>  function($sl){			        
        			$authService = $sl->get('Zend\Authentication\AuthenticationService');
					$loggedUser = $authService->getIdentity()['user'];							
					return new Service\TaskOwnerAndOngoingOrAcceptedTaskAssertion($loggedUser);
				},
        		'TaskOwnerAndCompletedTaskWithEstimationProcessCompletedAssertion' =>  function($sl){			        
        			$authService = $sl->get('Zend\Authentication\AuthenticationService');
					$loggedUser = $authService->getIdentity()['user'];							
					return new Service\TaskOwnerAndCompletedTaskWithEstimationProcessCompletedAssertion($loggedUser);
				},
        		'TaskMemberAndAcceptedTaskAssertion' =>  function($sl){			        
        			$authService = $sl->get('Zend\Authentication\AuthenticationService');
					$loggedUser = $authService->getIdentity()['user'];							
					return new Service\TaskMemberAndAcceptedTaskAssertion($loggedUser);
				},
				'Authorization\CurrentUserProvider' => function($locator){
					$authService = $locator->get('Zend\Authentication\AuthenticationService');
					$loggedUser = $authService->getIdentity()['user'];
					return $loggedUser;
	        	},	
            ),
		);
    }
}

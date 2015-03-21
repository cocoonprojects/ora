<?php

namespace TaskManagement;

use Zend\Mvc\MvcEvent;
use Zend\ModuleManager\Feature\AutoloaderProviderInterface;
use Zend\ModuleManager\Feature\ConfigProviderInterface;
use Zend\EventManager\Event;
use TaskManagement\Controller\MembersController;
use TaskManagement\Controller\TasksController;
use TaskManagement\Controller\TransitionsController;
use TaskManagement\Controller\EstimationsController;
use TaskManagement\Controller\SharesController;
use TaskManagement\Controller\StreamsController;
use TaskManagement\Assertion\MemberOfNotAcceptedTaskAssertion;
use TaskManagement\Service\TransferTaskSharesCreditsListener;

class Module implements AutoloaderProviderInterface, ConfigProviderInterface
{    
	public function onBootstrap(MvcEvent $e)
	{
		$application = $e->getApplication();
		$eventManager = $application->getEventManager();
		$serviceManager = $application->getServiceManager();
		
		$serviceManager->get('TaskManagement\TaskCommandsOberver');
		$serviceManager->get('TaskManagement\StreamCommandsOberver');
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
            ),
            'factories' => array(
	            'TaskManagement\Controller\Tasks' => function ($sm) {
	            	$locator = $sm->getServiceLocator();
	            	$taskService = $locator->get('TaskManagement\TaskService');
	            	$streamService = $locator->get('TaskManagement\StreamService');
	            	$authorize = $locator->get('BjyAuthorize\Service\Authorize');
	            	$accountService = $locator->get('Accounting\CreditsAccountsService');
	            	$controller = new TasksController($taskService, $streamService, $authorize);
	            	$controller->setAccountService($accountService);
	            	return $controller;
	            },
	            'TaskManagement\Controller\Members' => function ($sm) {
            		$locator = $sm->getServiceLocator();
            		$taskService = $locator->get('TaskManagement\TaskService');
            		$accountService = $locator->get('Accounting\CreditsAccountsService');
            		$controller = new MembersController($taskService);
					$controller->setAccountService($accountService);
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
            	},
            	'TaskManagement\Controller\Streams' => function ($sm) {
            		$locator = $sm->getServiceLocator();
            		$streamService = $locator->get('TaskManagement\StreamService');
            		$organizationService = $locator->get('User\OrganizationService');
            		$controller = new StreamsController($streamService, $organizationService);
            		return $controller;
              	}
            )
        );        
    } 
    
    public function getServiceConfig()
    {
        return array (
            'invokables' => array(
	            'TaskManagement\CloseTaskListener' => 'TaskManagement\Service\CloseTaskListener',
            ),
        	'factories' => array (
                'TaskManagement\StreamService' => 'TaskManagement\Service\StreamServiceFactory',
            	'TaskManagement\TaskService' => 'TaskManagement\Service\TaskServiceFactory',
				'TaskManagement\KanbanizeService' => 'TaskManagement\Service\KanbanizeServiceFactory',

			    'TaskManagement\MemberOfOrganizationAssertion' =>  function($sl){			        
        			$authService = $sl->get('Zend\Authentication\AuthenticationService');
					$loggedUser = $authService->getIdentity()['user'];							
					return new Service\MemberOfOrganizationAssertion($loggedUser);					
			    },
				'TaskManagement\MemberOfNotAcceptedTaskAssertion' =>  function($sl){			        
        			$authService = $sl->get('Zend\Authentication\AuthenticationService');
					$loggedUser = $authService->getIdentity()['user'];							
					return new MemberOfNotAcceptedTaskAssertion($loggedUser);
				},
				'TaskManagement\OrganizationMemberNotTaskMemberAndNotCompletedTaskAssertion' =>  function($sl){			        
        			$authService = $sl->get('Zend\Authentication\AuthenticationService');
					$loggedUser = $authService->getIdentity()['user'];							
					return new Service\OrganizationMemberNotTaskMemberAndNotCompletedTaskAssertion($loggedUser);
				},
        		'TaskManagement\TaskMemberNotOwnerAndNotCompletedTaskAssertion' =>  function($sl){			        
        			$authService = $sl->get('Zend\Authentication\AuthenticationService');
					$loggedUser = $authService->getIdentity()['user'];							
					return new Service\TaskMemberNotOwnerAndNotCompletedTaskAssertion($loggedUser);
				},
				'TaskManagement\TaskOwnerAndNotCompletedTaskAssertion' => function($sl){			        
        			$authService = $sl->get('Zend\Authentication\AuthenticationService');
					$loggedUser = $authService->getIdentity()['user'];							
					return new Service\TaskOwnerAndNotCompletedTaskAssertion($loggedUser);
				},
        		'TaskManagement\TaskMemberNotOwnerAndOpenOrCompletedTaskAssertion' =>  function($sl){			        
        			$authService = $sl->get('Zend\Authentication\AuthenticationService');
					$loggedUser = $authService->getIdentity()['user'];							
					return new Service\TaskMemberNotOwnerAndOpenOrCompletedTaskAssertion($loggedUser);
				},
        		'TaskManagement\TaskOwnerAndOngoingOrAcceptedTaskAssertion' =>  function($sl){			        
        			$authService = $sl->get('Zend\Authentication\AuthenticationService');
					$loggedUser = $authService->getIdentity()['user'];							
					return new Service\TaskOwnerAndOngoingOrAcceptedTaskAssertion($loggedUser);
				},
        		'TaskManagement\TaskOwnerAndCompletedTaskWithEstimationProcessCompletedAssertion' =>  function($sl){			        
        			$authService = $sl->get('Zend\Authentication\AuthenticationService');
					$loggedUser = $authService->getIdentity()['user'];							
					return new Service\TaskOwnerAndCompletedTaskWithEstimationProcessCompletedAssertion($loggedUser);
				},
        		'TaskManagement\TaskMemberAndAcceptedTaskAssertion' =>  function($sl){			        
        			$authService = $sl->get('Zend\Authentication\AuthenticationService');
					$loggedUser = $authService->getIdentity()['user'];							
					return new Service\TaskMemberAndAcceptedTaskAssertion($loggedUser);
				},
				'Authorization\CurrentUserProvider' => function($locator){
					$authService = $locator->get('Zend\Authentication\AuthenticationService');
					$loggedUser = $authService->getIdentity()['user'];
					return $loggedUser;
	        	},	
				'TaskManagement\KanbanizeService' => 'TaskManagement\Service\KanbanizeServiceFactory',
            	'TaskManagement\TaskCommandsOberver' => 'TaskManagement\Service\TaskCommandsObserverFactory',
            	'TaskManagement\StreamCommandsOberver' => 'TaskManagement\Service\StreamCommandsObserverFactory',
            	'TaskManagement\TransferTaskSharesCreditsListener' => function ($locator) {
            		$taskService = $locator->get('TaskManagement\TaskService');            		
            		$streamService = $locator->get('TaskManagement\StreamService');
            		$organizationService = $locator->get('User\OrganizationService');
            		$accountService = $locator->get('Accounting\CreditsAccountsService');
            		$eventStore = $locator->get('prooph.event_store');
            		$rv = new TransferTaskSharesCreditsListener($taskService, $streamService, $organizationService, $accountService, $eventStore);
            		return $rv;         		
            	},
            ),
		);
    }
}

<?php

namespace TaskManagement;

use Zend\ModuleManager\Feature\AutoloaderProviderInterface;
use Zend\ModuleManager\Feature\ConfigProviderInterface;
use TaskManagement\Controller\MembersController;
use TaskManagement\Controller\TasksController;
use TaskManagement\Controller\TransitionsController;
use TaskManagement\Controller\EstimationsController;
use TaskManagement\Controller\SharesController;
use TaskManagement\Controller\StreamsController;
use TaskManagement\Service\TransferTaskSharesCreditsListener;
use TaskManagement\Service\StreamCommandsListener;
use TaskManagement\Service\TaskCommandsListener;
use Zend\Permissions\Acl\Assertion\AssertionInterface;


class Module implements AutoloaderProviderInterface, ConfigProviderInterface
{        
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
            		$organizationService = $locator->get('Application\OrganizationService');
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
        		'TaskManagement\MemberOfOrganizationAssertion' => 'TaskManagement\Assertion\MemberOfOrganizationAssertion',
        		'TaskManagement\MemberOfNotAcceptedTaskAssertion' => 'TaskManagement\Assertion\MemberOfNotAcceptedTaskAssertion',
        		'TaskManagement\OrganizationMemberNotTaskMemberAndNotCompletedTaskAssertion' => 'TaskManagement\Assertion\OrganizationMemberNotTaskMemberAndNotCompletedTaskAssertion',
        		'TaskManagement\TaskMemberNotOwnerAndNotCompletedTaskAssertion' => 'TaskManagement\Assertion\TaskMemberNotOwnerAndNotCompletedTaskAssertion',
        		'TaskManagement\TaskOwnerAndNotCompletedTaskAssertion' => 'TaskManagement\Assertion\TaskOwnerAndNotCompletedTaskAssertion',
        		'TaskManagement\OwnerOfOpenOrCompletedTaskAssertion' => 'TaskManagement\Assertion\OwnerOfOpenOrCompletedTaskAssertion',
        		'TaskManagement\TaskOwnerAndOngoingOrAcceptedTaskAssertion' => 'TaskManagement\Assertion\TaskOwnerAndOngoingOrAcceptedTaskAssertion',
        		'TaskManagement\TaskOwnerAndCompletedTaskWithEstimationProcessCompletedAssertion' => 'TaskManagement\Assertion\TaskOwnerAndCompletedTaskWithEstimationProcessCompletedAssertion',
        		'TaskManagement\TaskMemberAndAcceptedTaskAssertion' => 'TaskManagement\Assertion\TaskMemberAndAcceptedTaskAssertion'
            ),
        	'factories' => array (
                'TaskManagement\StreamService' => 'TaskManagement\Service\StreamServiceFactory',
            	'TaskManagement\TaskService' => 'TaskManagement\Service\TaskServiceFactory',
				'TaskManagement\KanbanizeService' => 'TaskManagement\Service\KanbanizeServiceFactory',
					
            	'TaskManagement\TaskCommandsListener' => function ($locator) {
            		$entityManager = $locator->get('doctrine.entitymanager.orm_default');
            		$rv = new TaskCommandsListener($entityManager);
            		$kanbanizeService = $locator->get('TaskManagement\KanbanizeService');
            		$rv->setKanbanizeService($kanbanizeService);
            		return $rv;
            	},
            	'TaskManagement\StreamCommandsListener' => function ($locator) {
            		$entityManager = $locator->get('doctrine.entitymanager.orm_default');
            		return new StreamCommandsListener($entityManager);
            	},
            	
            	'TaskManagement\TransferTaskSharesCreditsListener' => function ($locator) {
            		$taskService = $locator->get('TaskManagement\TaskService');            		
            		$streamService = $locator->get('TaskManagement\StreamService');
            		$organizationService = $locator->get('Application\OrganizationService');
            		$accountService = $locator->get('Accounting\CreditsAccountsService');
            		$eventStore = $locator->get('prooph.event_store');
            		$rv = new TransferTaskSharesCreditsListener($taskService, $streamService, $organizationService, $accountService, $eventStore);
            		return $rv;         		
            	},
            ),
            'initializers' => array(
			    function ($instance, $locator) {
			        if ($instance instanceof AssertionInterface) {
			        	$authService = $locator->get('Zend\Authentication\AuthenticationService');
						$loggedUser = $authService->getIdentity()['user'];	
			            $instance->setLoggedUser($loggedUser);
			        }
			    }
			)
		);
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
}

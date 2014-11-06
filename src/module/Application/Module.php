<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2014 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Application;

use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;
use Prooph\EventStore\PersistenceEvent\PostCommitEvent;
use Doctrine\ORM\EntityManager;
use Application\Controller\AuthController;

class Module
{
	/**
	 * 
	 * @var EntityManager
	 */
// 	private $entityManager;
	
    public function onBootstrap(MvcEvent $e)
    {
    	$application = $e->getApplication();
        $eventManager = $application->getEventManager();
        $serviceManager = $application->getServiceManager();
        
        $moduleRouteListener = new ModuleRouteListener();
        $moduleRouteListener->attach($eventManager);        
        
//         $eventStore = $serviceManager->get('prooph.event_store');
//         $this->entityManager = $serviceManager->get('doctrine.entitymanager.orm_default');
// 		$eventStore->getPersistenceEvents()->attach('commit.post', array($this, 'postCommitEvent'));        
    }
    
//     public function postCommitEvent(PostCommitEvent $event) {
// 		foreach ($event->getRecordedEvents() as $streamEvent) {
// 			$this->entityManager->persist($streamEvent->getEntity());
//         }
//         $this->entityManager->flush();
//     }
    
    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }
    
    public function getControllerConfig() 
    {
        return array(
            'invokables' => array(
                'Application\Controller\Index' => 'Application\Controller\IndexController',
            ),
            'factories' => array(
            	'Application\Controller\Auth'  => function ($sm) {
            		$locator = $sm->getServiceLocator();
            		$providers = $locator->get('OAuth2\Providers');
            		$authService = $locator->get('Application\Service\AuthenticationService');
            		$controller = new AuthController($authService, $providers);
            		return $controller;
            	},
            )
        );        
    } 
    
    public function getServiceConfig()
    {
        return array(
            'factories' => array(
            	'Application\Service\AuthenticationService' => 'Application\Service\AuthenticationServiceFactory',
            	'Zend\Log\Logger' => function($sm){
            		
	                $logger = new Zend\Log\Logger;
	                $writer = new Zend\Log\Writer\Stream('./data/log/'.date('Y-m-d').'-error.log');
	                 
	                $logger->addWriter($writer);  
	                
	                return $logger;
	            },
	            'OAuth2\Providers' => 'Application\Service\OAuth2ProvidersFactory',
            ),
        );
    }

    public function getViewHelperConfig()
    {
    	return array(
    			'invokables' => array(
    				'UserBoxHelper' => 'Application\View\Helper\UserBoxHelper',
    				'LoginPopupHelper' => 'Application\View\Helper\LoginPopupHelper',
    			),
    	);
    }
        
    public function getAutoloaderConfig()
    {    	
        return array(
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
     				'Ora' => __DIR__ . '/../../library/Ora'            
                ),
            ),
        );
    }
}

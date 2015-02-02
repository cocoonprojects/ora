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
use Zend\Log\Writer\Stream;
use Zend\Log\Logger;
use Application\Service\IdentityRolesProvider;

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
    }
    
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
					$resolver = $locator->get('Application\Service\AdapterResolver');
            		$authService = $locator->get('Zend\Authentication\AuthenticationService');
            		$userService = $locator->get('User\UserService');
            		$controller = new AuthController($authService, $resolver);
            		$controller->setUserService($userService);
            		return $controller;
            	},            	
            )
        );        
    } 
    
	public function getControllerPluginConfig()
	{
	    return array(
	        'factories' => array(
	            'transaction' => 'Application\Controller\Plugin\TransactionPluginFactory',
	        	'loggedIdentity' => 'Application\Controller\Plugin\LoggedIdentityPluginFactory',
	        ),
	    );
	}
	
    public function getServiceConfig()
    {
        return array(
            'factories' => array(
                'Zend\Authentication\AuthenticationService' => 'Application\Service\AuthenticationServiceFactory',
                'Application\Service\AdapterResolver' => 'Application\Service\OAuth2AdapterResolverFactory',
                'Zend\Log' => function ($sl) {
                    $writer = new Stream('/vagrant/src/data/logs/application.log');
                    $logger = new Logger();
                    $logger->addWriter($writer);
                    return $logger;
                },
                
                'Application\Service\IdentityRolesProvider' =>
                 function($serviceLocator){
                    
                    $authService = 
                        $serviceLocator->get('Zend\Authentication\AuthenticationService');
                    $provider = new IdentityRolesProvider($authService);
                    return $provider;
                },
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

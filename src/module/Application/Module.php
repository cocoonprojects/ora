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

class Module
{
	private $providerInstanceList = array();
	
    public function onBootstrap(MvcEvent $e)
    {
    	$application = $e->getApplication();
        $eventManager        = $application->getEventManager();
        $moduleRouteListener = new ModuleRouteListener();
        $moduleRouteListener->attach($eventManager);
        
        $serviceManager = $application->getServiceManager();
        
        $this->initAuthProviders($serviceManager);
        
        $serviceManager->setFactory('providerInstanceList', function ($serviceManager) {
        
        	return $this->providerInstanceList;
        });
    }
    
    private function initAuthProviders($serviceManager)
    {
    	$allConfigurationOption = $serviceManager->get('Config');
    	 
    	if(is_array($allConfigurationOption) &&
    			array_key_exists('zendoauth2', $allConfigurationOption))
    	{
    		$availableProviderList = $allConfigurationOption['zendoauth2'];
    		 
    		foreach($availableProviderList as $provider => $providerOptions)
    		{
    			$provider = ucfirst($provider);
    			$instanceProviderName = "ZendOAuth2\\".$provider;
    			$instanceProvider = $serviceManager->get($instanceProviderName);
    			 
    			if(null != $instanceProvider)
    			{
    				$this->providerInstanceList[$provider] =  $instanceProvider;
    			}
    		}
    	}
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
            	'Application\Controller\Auth'  => 'Application\Controller\AuthController'
            )
        );        
    } 
    
    public function getServiceConfig()
    {
        return array(
            'factories' => array(
                'Application\Service\EventStore' => 'Application\Service\EventStoreFactory',
            	'Application\Service\AuthenticationService' => 'Application\Service\AuthenticationServiceFactory',
            	'Zend\Log\Logger' => function($sm){
            		
	                $logger = new Zend\Log\Logger;
	                $writer = new Zend\Log\Writer\Stream('./data/log/'.date('Y-m-d').'-error.log');
	                 
	                $logger->addWriter($writer);  
	                
	                return $logger;
	            },
            	          		
            ),
            'invokables' => array(
            	'Application\Auth\Adapter' => 'Application\Authentication\Adapter\Auth',
            ),            

        );
    }

    public function getViewHelperConfig()
    {
    	return array(
    			'invokables' => array(
    					'UserBoxHelper' => 'Application\View\Helper\UserBoxHelper',
    					'LoginPopupHelper' => 'Application\View\Helper\LoginPopupHelper'
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

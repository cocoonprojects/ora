<?php
namespace Auth;

class Module
{
    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

     public function onBootstrap(\Zend\Mvc\MvcEvent $e)
     {
        $application = $e->getApplication();
        $em = $application->getEventManager();
        $em->attach(\Zend\Mvc\MvcEvent::EVENT_DISPATCH, array($this, 'onDispatch'));
        
        $serviceManager = $application->getServiceManager();
        
        $serviceManager->setFactory('providerInstanceList', function ($serviceManager) {
        	
        	$providerInstanceList = array();
        	
        	$allConfigurationOption = $serviceManager->get('Config');
        	
        	if(is_array($allConfigurationOption) && array_key_exists('zendoauth2', $allConfigurationOption))
        	{
        		$availableProviderList = $allConfigurationOption['zendoauth2'];
        			
        		foreach($availableProviderList as $provider => $providerOptions)
        		{
        			$provider = ucfirst($provider);
        			$instanceProviderName = "ZendOAuth2\\".$provider;
        			$instanceProvider = $serviceManager->get($instanceProviderName);
        				
        			if(null != $instanceProvider)
        			{        				
        				$providerInstanceList[$provider] =  $instanceProvider;
        			}
        		}
        	}

        	return $providerInstanceList;
        });        
    }
    
    public function onDispatch(\Zend\Mvc\MvcEvent $e)
    {
    }

    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                ),
            ),
        );
    }

    public function getControllerConfig() {
        
        return array(
            'invokables' => array(
                'Auth\Controller\Login' => 'Auth\Controller\LoginController',
                'Auth\Controller\Logout' => 'Auth\Controller\LogoutController',
            ),
        );
        
    } 
    
    public function getServiceConfig()
    {
    	return array(
    			'factories' => array(
    					'Auth\Service\AuthenticationService' => 'Auth\Service\AuthenticationServiceFactory',
    			)
    	);
    }    
    
    public function getViewHelperConfig()
    {
    	return array(
    			'invokables' => array(
    					'authenticationAction' => 'Auth\View\Helper\AuthenticationAction',
    					'popupProviderList' => 'Auth\View\Helper\PopupProviderList'
    			),
    	);
    }    
}

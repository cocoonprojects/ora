<?php
namespace Auth;

use Ora\User\Role;

class Module
{
	private $providerInstanceList = array();
	
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
    			),
    			'invokables' => array(
    					'Auth\Adapter' => 'Auth\Authentication\Adapter\Auth',
    			),
    	);
    }    
    
    public function getViewHelperConfig()
    {
    	return array(
    			'invokables' => array(
    					'UserBoxHelper' => 'Auth\View\Helper\UserBoxHelper',
    					'LoginPopupHelper' => 'Auth\View\Helper\LoginPopupHelper'
    			),
    	);
    }    
}
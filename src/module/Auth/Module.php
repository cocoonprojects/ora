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
        $em = $e->getApplication()->getEventManager();
        
        $em->attach(\Zend\Mvc\MvcEvent::EVENT_DISPATCH, array($this, 'onDispatch'));
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
    					'informationsOfAuthentication' => 'Auth\View\Helper\InformationsOfAuthentication'
    			),
    	);
    }    
}

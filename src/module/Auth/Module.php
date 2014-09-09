<?php
namespace Auth;

class Module
{
    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
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

    // Service Manager Configuration
    public function getServiceConfig() 
    {
        return array(
            'factories' => array(
                'Auth\Service\AuthService' => 'Auth\Service\AuthServiceFactory'
            )
        );
    }
}

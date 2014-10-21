<?php

namespace User;

<<<<<<< HEAD
use Zend\ModuleManager\Feature\AutoloaderProviderInterface;
use Zend\ModuleManager\Feature\ConfigProviderInterface;

class Module implements AutoloaderProviderInterface, ConfigProviderInterface
=======
use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;

class Module 
>>>>>>> User module
{    
    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }
<<<<<<< HEAD

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
   
=======
    
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
        
>>>>>>> User module
    public function getServiceConfig()
    {
        return array (
            'factories' => array (
<<<<<<< HEAD
                'User\UserService' => 'User\Service\UserServiceFactory'
=======
                'User\Service\UserService' => 'User\Service\UserServiceFactory',
>>>>>>> User module
            ),
        );
    }
}
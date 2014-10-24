<?php

namespace User;

<<<<<<< HEAD
use Zend\ModuleManager\Feature\AutoloaderProviderInterface;
use Zend\ModuleManager\Feature\ConfigProviderInterface;
use Zend\Stdlib\InitializableInterface;

class Module implements AutoloaderProviderInterface, ConfigProviderInterface
=======
use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;

class Module 
>>>>>>> 51d59580bd364c75b14af06af0f49ef7f812f90c
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
        
>>>>>>> 51d59580bd364c75b14af06af0f49ef7f812f90c
    public function getServiceConfig()
    {
        return array (
            'factories' => array (
<<<<<<< HEAD
                'User\UserService' => 'User\Service\UserServiceFactory'
            ),
        );
    }

    public function onBootstrap($e)
    {
        $sm = $e->getApplication()->getServiceManager();
    
        $controllers = $sm->get('ControllerLoader');
    
        $controllers->addInitializer(function($controller, $cl) {
            if ($controller instanceof InitializableInterface) {
                $controller->init();
            }
        }, false); // false tells the loader to run this initializer after all others
    }
=======
                'User\Service\UserService' => 'User\Service\UserServiceFactory',
            ),
        );
    }
>>>>>>> 51d59580bd364c75b14af06af0f49ef7f812f90c
}
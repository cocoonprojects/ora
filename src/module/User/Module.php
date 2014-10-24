<?php

namespace User;

use Zend\ModuleManager\Feature\AutoloaderProviderInterface;
use Zend\ModuleManager\Feature\ConfigProviderInterface;
use Zend\Stdlib\InitializableInterface;

class Module implements AutoloaderProviderInterface, ConfigProviderInterface
{    
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
    
    public function getServiceConfig()
    {
        return array (
            'factories' => array (
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
}
<?php

namespace TasksManagement;

use Zend\ModuleManager\Feature\AutoloaderProviderInterface;
use Zend\ModuleManager\Feature\ConfigProviderInterface;
use TasksManagement\Service\ProjectServiceImpl;

class Module implements AutoloaderProviderInterface, ConfigProviderInterface
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
    
    public function getServiceConfig(){
    	
    	return array(
    		'factories' => array(
    				'TasksManagement\Services\ProjectService' => function($sm){
    					return new ProjectServiceImpl();
    				}
    			)
    	);
    }
}

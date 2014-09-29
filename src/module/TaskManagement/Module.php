<?php

namespace TaskManagement;

use Zend\ModuleManager\Feature\AutoloaderProviderInterface;
use Zend\ModuleManager\Feature\ConfigProviderInterface;
//use Ora\TaskManagement\EventSourcingTaskService;
//use Ora\EventStore\DoctrineEventStore;

class Module implements AutoloaderProviderInterface, ConfigProviderInterface
{
    //private $taskService;
    
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
                'TaskManagement\TaskService' => 'TaskManagement\ServiceFactory\TaskServiceFactory',
                //TODO: RIMUOVERE COMMENTI IN TUTTO IL FILE - VECCHIO METODO DI ANDREA
                /*'TaskManagement\TaskService' => function ($sm) {
                    $em = $sm->get('doctrine.entitymanager.orm_default');
                    
                    if(is_null($this->taskService)) {
                        $this->taskService = new EventSourcingTaskService(DoctrineEventStore::instance($em));
                    }
                    
                    return $this->taskService;
                },*/
            ),
        );
    }

}
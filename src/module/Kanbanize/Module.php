<?php
namespace Kanbanize;

use Zend\ModuleManager\Feature\AutoloaderProviderInterface;
use Zend\ModuleManager\Feature\ConfigProviderInterface;
use Ora\Kanbanize\KanbanizeServiceImpl;

class Module implements AutoloaderProviderInterface, ConfigProviderInterface
{
	public function getControllerConfig() 
	{
		return array(
			'invokables' => array(
			),
			'factories' => array(
			)
		);
	}
	
	public function getServiceConfig()
	{
		return array (
			'invokables' => array(
			),
			'factories' => array (
				'Kanbanize\SyncService' => function ($locator) {
					$config = $locator->get('Config');
					$apiKey	= $config['kanbanize']['apikey'];
					$url	= $config['kanbanize']['url'];
					
					$api = new KanbanizeAPI();
					$api->setApiKey($apiKey);
					$api->setUrl($url);
					
					return new KanbanizeServiceImpl($api);
				},
			),
			'initializers' => array(
			)
		);
	}

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
}
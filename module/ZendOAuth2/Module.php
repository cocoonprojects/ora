<?php

namespace ZendOAuth2;

use Zend\ModuleManager\Feature\AutoloaderProviderInterface;

class Module implements AutoloaderProviderInterface  
{
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

	public function getConfig($env = null)
	{
		return include __DIR__ . '/config/module.config.php';
	}
	
}

<?php

namespace TaskManagement\Service;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Ora\Kanbanize\KanbanizeServiceImpl;
use Ora\EntitySerializer;
use Ora\Kanbanize\KanbanizeAPI;

class KanbanizeServiceFactory implements FactoryInterface 
{
	private static $instance;
	
	public function createService(ServiceLocatorInterface $serviceLocator) 
	{
		if(is_null(self::$instance)) {
			$config = $serviceLocator->get('Config');
			$apiKey = $config['kanbanize']['apikey'];
			$url = $config['kanbanize']['url'];
			$api = new KanbanizeAPI();
			$api->setApiKey($apiKey);
			$api->setUrl($url);
				
			self::$instance = new KanbanizeServiceImpl($api);
		}
		return self::$instance;
	}
}
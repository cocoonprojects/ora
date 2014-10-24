<?php

namespace TaskManagement\Service;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Ora\Kanbanize\KanbanizeServiceImpl;
use Ora\EntitySerializer;

class KanbanizeServiceFactory implements FactoryInterface 
{
	
	private static $instance;
	
	public function createService(ServiceLocatorInterface $serviceLocator) 
	{
		if(is_null(self::$instance)) {
			//$eventStore = $serviceLocator->get('Application\Service\EventStore');
			$entityManager = $serviceLocator->get('doctrine.entitymanager.orm_default');
			//$entitySerializer = new EntitySerializer($entityManager);
			
			$config = $serviceLocator->get('Config');
			
			$apiKey = $config['kanbanize']['apikey'];
			$url = $config['kanbanize']['url'];
			
			self::$instance = new KanbanizeServiceImpl($entityManager, $apiKey, $url);
		}
		return self::$instance;
	}
}
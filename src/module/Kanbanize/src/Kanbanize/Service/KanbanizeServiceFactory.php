<?php

namespace Kanbanize\Service;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class KanbanizeServiceFactory implements FactoryInterface 
{
	public function createService(ServiceLocatorInterface $serviceLocator) 
	{
		$service = new KanbanizeService();
		$config = $serviceLocator->get('Config');
		$service->setApiKey($config['kanbanize']['apikey']);
		$service->setUrl($config['kanbanize']['url']);
		return $service;
	}
}
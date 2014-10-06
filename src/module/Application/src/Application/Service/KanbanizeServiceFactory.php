<?php

namespace Application\Service;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class KanbanizeServiceFactory implements FactoryInterface 
{
	public function createService(ServiceLocatorInterface $serviceLocator) 
	{
		return new KanbanizeService();
	}
}
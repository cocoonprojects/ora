<?php
namespace Application\Service;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use ZendExtension\Authentication\OAuth2\MockOAuth2Adapter;

class MockOAuth2AdapterResolverFactory implements FactoryInterface
{
	private static $instance;
	
	public function createService(ServiceLocatorInterface $serviceLocator)
	{
		if(is_null(self::$instance)) {
			$userService = $serviceLocator->get('Application\UserService');
			self::$instance = new MockOAuth2Adapter($userService);
		}
	    return self::$instance;
	}
}
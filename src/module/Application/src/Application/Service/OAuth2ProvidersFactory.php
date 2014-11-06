<?php
namespace Application\Service;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class OAuth2ProvidersFactory implements FactoryInterface
{
	private static $providers = array();	
	
	public function createService(ServiceLocatorInterface $serviceLocator) {
		if(empty(self::$providers)) {
			$config = $serviceLocator->get('Config');
			if(is_array($config) && array_key_exists('zendoauth2', $config)) {
				foreach($config['zendoauth2'] as $k => $v) {
					self::$providers[$k] = $serviceLocator->get('ZendOAuth2\\'.ucfirst($k));
				}
			}
		}
		return self::$providers;
	}
}
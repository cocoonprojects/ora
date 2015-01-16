<?php
namespace Application\Service;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use ZendExtension\Authentication\OAuth2\OAuth2AdapterResolver;

class OAuth2AdapterResolverFactory implements FactoryInterface
{
	private static $instance;	
	
	public function createService(ServiceLocatorInterface $serviceLocator) {
		if(is_null(self::$instance)) {
			$adapter = $serviceLocator->get('ZendOAuth2\Auth\Adapter');
			$config = $serviceLocator->get('Config');
			$providers = $this->loadProviders($config, $serviceLocator);
			self::$instance = new OAuth2AdapterResolver($adapter, $providers);
		}
		return self::$instance;
	}
	
	private function loadProviders($config, ServiceLocatorInterface $serviceLocator) {
		$rv = array();
		if(is_array($config) && array_key_exists('zendoauth2', $config)) {
			foreach($config['zendoauth2'] as $k => $v) {
				$rv[$k] = $serviceLocator->get('ZendOAuth2\\'.ucfirst($k));
			}
		}
		return $rv;		
	}
}
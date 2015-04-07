<?php
namespace Application\Authentication\OAuth2;

use Application\Authentication\AdapterResolver;
use ZendOAuth2\Authentication\Adapter\ZendOAuth2;
use Zend\Mvc\Controller\AbstractController;

class OAuth2AdapterResolver implements AdapterResolver {
	
	/**
	 * 
	 * @var ZendOAuth2Adapter
	 */
	private $adapter;
	private $providers;
	
	public function __construct(ZendOAuth2 $adapter, $providers) {
		$this->adapter = $adapter;
		$this->providers = $providers;
	}
	
	public function getAdapter(AbstractController $controller) {
		$id = $controller->params('id');
		if(empty($id) || !isset($this->providers[$id]))
		{
			return null;
		}
		$provider = $this->providers[$id];
		if(!$provider->getToken($controller->getRequest()))
		{
			$msg = $provider->getError()['internal-error'];
			throw new InvalidTokenException($msg);
		}
		
		$this->adapter->setOAuth2Client($provider);
		return $this->adapter;
	}
	
	public function getProviders() {
		return $this->providers;
	}
}
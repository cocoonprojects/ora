<?php
namespace Auth\View\Helper;

use Zend\View\Helper\AbstractHelper;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class InformationsOfAuthentication extends AbstractHelper implements ServiceLocatorAwareInterface
{	
	public function __invoke()
	{		
		$viewVariables['logged'] = false;
		$viewVariables['urlAuthList'] = array();
		$viewVariables['user'] = "";
						
		$authenticationService = new \Zend\Authentication\AuthenticationService();
		
		if($authenticationService->hasIdentity())
		{
			$viewVariables['logged'] = true;
			$viewVariables['user'] = $authenticationService->getIdentity();
		}
		else
		{
			$urlList = array();
			$helperPluginManager = $this->getServiceLocator();
			$serviceManager = $helperPluginManager->getServiceLocator();
						
			$allConfigurationOption = $serviceManager->get('Config');
			
			if(is_array($allConfigurationOption) && array_key_exists('zendoauth2', $allConfigurationOption))
			{
				$availableProviderList = $allConfigurationOption['zendoauth2'];
				
				foreach($availableProviderList as $provider => $providerOptions)
				{
					$provider = ucfirst($provider);
					$instanceProviderName = "ZendOAuth2\\".$provider;
    				$instanceProvider = $serviceManager->get($instanceProviderName);
						
					if(null != $instanceProvider)
						$urlList[$provider] =  $instanceProvider->getUrl();
				}				
			}			
			
			$viewVariables['urlAuthList'] = $urlList;
		}		
		
		return $viewVariables;				
	}
	
	public function setServiceLocator(ServiceLocatorInterface $serviceLocator)
	{
		$this->serviceLocator = $serviceLocator;
		return $this;
	}

	public function getServiceLocator()
	{
		return $this->serviceLocator;
	}	
}
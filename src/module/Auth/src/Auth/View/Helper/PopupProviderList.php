<?php
namespace Auth\View\Helper;

use Zend\View\Helper\AbstractHelper;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class PopupProviderList extends AbstractHelper implements ServiceLocatorAwareInterface
{	
	public function __invoke()
	{				
		$serviceLocator = $this->getServiceLocator();		
		$authenticationService = $serviceLocator->get('Auth\Service\AuthenticationService');
	
		if(!$authenticationService->hasIdentity())
		{
			$urlList = array();
			$serviceLocator = $this->getServiceLocator();
			
			$allConfigurationOption = $serviceLocator->get('Config');
				
			if(is_array($allConfigurationOption) && array_key_exists('zendoauth2', $allConfigurationOption))
			{
				$availableProviderList = $allConfigurationOption['zendoauth2'];
			
				foreach($availableProviderList as $provider => $providerOptions)
				{
					$provider = ucfirst($provider);
					$instanceProviderName = "ZendOAuth2\\".$provider;
					$instanceProvider = $serviceLocator->get($instanceProviderName);
			
					if(null != $instanceProvider)
						$urlList[$provider] =  $instanceProvider->getUrl();
				}
			}
			
			$output = "<div id='popupLogin' class='modal fade'>
					<div class='modal-dialog'>
						<div class='modal-content'>
							<div class='modal-header'>
								<button type='button' class='close' data-dismiss='modal'>
									<span aria-hidden='true'>&times;</span>
									<span class='sr-only'>Close</span>
								</button>
								<h4 class='modal-title'>Effettua il login</h4>
							</div>
							<div class='modal-body'>
								<center>";
							
				foreach($urlList as $provider => $url)
				{
					$output .=  "<a onclick=\"auth.openAuthWindow('{$url}'); return false;\" class='btn btn-success btn-lg' href='#'>Login con {$provider}</a>";
				}
			
			$output .=" </center>
		              </div>
		            </div><!-- /.modal-content -->
		          </div><!-- /.modal-dialog -->
		        </div><!-- /.modal -->";			
			
			return $output;		
		}		
	}
		
	public function setServiceLocator(ServiceLocatorInterface $helperPluginManager)
	{
		$this->serviceLocator = $helperPluginManager->getServiceLocator();
		return $this;
	}

	public function getServiceLocator()
	{
		return $this->serviceLocator;
	}	
}
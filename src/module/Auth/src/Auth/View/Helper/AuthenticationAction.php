<?php
namespace Auth\View\Helper;

use Zend\View\Helper\AbstractHelper;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class AuthenticationAction extends AbstractHelper implements ServiceLocatorAwareInterface
{	
	public function __invoke()
	{				
		$serviceLocator = $this->getServiceLocator();		
		$authenticationService = $serviceLocator->get('Auth\Service\AuthenticationService');
		
		if($authenticationService->hasIdentity())
		{
			$user = $authenticationService->getIdentity();
			
			$output = "<li><img src='{$user['picture']}' alt='picture' class='img-circle' style='max-width: 40px'></li>";
			$output .= "<li><a>{$user['name']}</a></li>";
			$output .= "<li><a href='/auth/logout'>Logout</a></li>";
			
			return $output;		
		}

		$output = "<li><a id='login-auth'>Login</a></li>";

		return $output;
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
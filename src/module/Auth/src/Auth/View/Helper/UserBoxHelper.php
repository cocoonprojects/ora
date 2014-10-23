<?php
namespace Auth\View\Helper;

use Zend\View\Helper\AbstractHelper;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class UserBoxHelper extends AbstractHelper implements ServiceLocatorAwareInterface
{	
	public function __invoke()
	{				
		$serviceLocator = $this->getServiceLocator();		
		$authenticationService = $serviceLocator->get('Auth\Service\AuthenticationService');
		
		if($authenticationService->hasIdentity())
		{
			$identity = $authenticationService->getIdentity();
			
			$output = "<li><a>{$identity['user']->getFirstname()} {$identity['user']->getLastname()}</a></li>";
			$output .= "<li><a href='/auth/logout'>Logout</a></li>";				
		}
		else 
		{
			$output = "<li><a id='login-auth'>Login</a></li>";
		}

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
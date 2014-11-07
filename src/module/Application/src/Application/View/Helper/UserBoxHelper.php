<?php
namespace Application\View\Helper;

use Zend\View\Helper\AbstractHelper;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class UserBoxHelper extends AbstractHelper implements ServiceLocatorAwareInterface
{
	/**
	 * 
	 * @var \Zend\Di\ServiceLocatorInterface
	 */
	private $serviceLocator;
	
	public function __invoke()
	{
		$authService = $this->getServiceLocator()->get('Application\Service\AuthenticationService');
		if($authService->hasIdentity())
		{
			$identity = $authService->getIdentity();
			
			$output = "<li><a>{$identity['user']->getFirstname()} {$identity['user']->getLastname()}</a></li>";
			$output .= "<li><a href='".$this->getView()->basePath()."/auth/logout'>Logout</a></li>";				
		}
		else 
		{
			$output = '<li><a href="#" id="login-auth">Login</a></li>';
		}

		return $output;
	}	
	
	public function setServiceLocator(ServiceLocatorInterface $helperPluginManager)
	{
		$this->serviceLocator = $helperPluginManager->getServiceLocator();
		return $this;
	}

	public function getServiceLocator() {
		return $this->serviceLocator;
	}
}
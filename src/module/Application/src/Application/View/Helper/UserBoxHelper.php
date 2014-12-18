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
		$rv = '<ul class="nav navbar-nav navbar-right">';
		if($authService->hasIdentity())
		{
			$identity = $authService->getIdentity()['user'];
			
			$rv .= '<li><a href="#" class="navbar-link">'.$identity->getFirstname().' '.$identity->getLastname().'</a></li>';
			$rv .= '<li><a class="" href="'.$this->getView()->basePath().'/auth/logout">Logout</a></li>';
		}
		else 
		{
			$rv .= '<li><a href="#" id="login-auth" class="">Login</a></li>';
		}
		$rv .= '</ul>';
		return $rv;
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
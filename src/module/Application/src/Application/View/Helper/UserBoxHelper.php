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
		$authService = $this->getServiceLocator()->get('Zend\Authentication\AuthenticationService');
		if($authService->hasIdentity())
		{
			$identity = $authService->getIdentity()['user'];
			$rv = '<ul class="nav navbar-nav navbar-right">
					<li><a href="'.$this->getView()->basePath().'/auth/logout">Logout</a></li>
					</ul>
					<div class="pull-right navbar-text">
					<img src="'.$identity->getPicture().'" alt="Avatar" style="max-width: 32px; max-height: 32px;" class="img-circle">
					'.$identity->getEmail().'</div>';
		}
		else 
		{
			$rv = '<ul class="nav navbar-nav navbar-right"><li><a href="#" data-toggle="modal" data-target="#loginModal">Sign in</a></li></ul>';
		}
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
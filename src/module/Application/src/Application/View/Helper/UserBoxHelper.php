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
			
// 			$rv .= '<li class="dropdown">
// 						<button class="btn btn-default dropdown-toggle" type="button" id="userDropdownMenu" data-toggle="dropdown" aria-expanded="true">'.$identity->getFirstname().' '.$identity->getLastname().'
// 								<span class="caret"></span>
// 						</button>
// 						<ul class="dropdown-menu" role="menu" aria-labelledby="userDropdownMenu">
// 							<li role="presentation">'.$identity->getEmail().'</li>
// 							<li role="presentation"><a role="menuitem" tabindex="-1" href="#">Edit Profile</a></li>
// 							<li role="presentation"><a role="menuitem" tabindex="-1" href="#">Something else here</a></li>
// 							<li role="presentation"><a role="menuitem" tabindex="-1" href="#">Separated link</a></li>
// 						</ul>
// 					</li>';
			$rv = '<ul class="nav navbar-nav navbar-right"><li><a href="'.$this->getView()->basePath().'/auth/logout">Logout</a></li></ul>';
			$rv .= '<p class="nav navbar-text navbar-right" style="margin-top: 15px; margin-bottom:15px;"><img src="'.$identity->getPicture().'" alt="Avatar" style="width: 32px; height: 32px;" class="img-circle"> '.$identity->getEmail().'</p>';
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
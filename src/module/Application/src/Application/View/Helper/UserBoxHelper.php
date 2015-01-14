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
		$rv = '<ul class="nav navbar-nav navbar-right">';
		if($authService->hasIdentity())
		{
			$identity = $authService->getIdentity()['user'];
			
			$rv .= '<li><p class="navbar-text">'.$identity->getEmail().'</p></li>';
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
			$rv .= '<li><a class="" href="'.$this->getView()->basePath().'/auth/logout">Logout</a></li>';
		}
		else 
		{
			$rv .= '<li><a href="#" data-toggle="modal" data-target="#loginModal" class="">Sign in</a></li>';
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
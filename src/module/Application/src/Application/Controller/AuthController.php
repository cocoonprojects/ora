<?php

namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Ora\Organization\Organization;

class AuthController extends AbstractActionController
{        
	private $authenticationService;
	private $userService;
	private $organizationService;
	private $redirectAfterLogout;
	private $instanceProvider;
		
	public function loginAction()
	{
		$resultAuthentication = array();
		 
		$provider = $this->params('id');
		$provider = ucfirst($provider);
		 		 
		$availableProviderList = $this->getServiceLocator()->get('providerInstanceList');
		 
		$view = new ViewModel();
		 
		if(strlen($this->params('code')) > 10)
		{
			//TODO: $this->getServiceLocator()->get('Zend\Log\Logger')->crit('Auth: Error code parameter: '.$this->getRequest()->getQuery('code'));
			$view->setVariable('error', 'Auth.InvalidCode');
			return $view;
		}
	
		if("" === $provider
				|| !array_key_exists($provider, $availableProviderList))
		{
			//TODO: $this->getServiceLocator()->get('Zend\Log\Logger')->crit('Auth: Error Provider parameter: '.$provider);
			$view->setVariable('error', 'Auth.InvalidProvider');
			return $view;
		}
		 
		$instanceProvider = $availableProviderList[$provider];
		 
		if(!$instanceProvider->getToken($this->request))
		{
			//TODO: $this->getServiceLocator()->get('Zend\Log\Logger')->crit('Auth: InvalidToken');
			$view->setVariable('error', 'Auth.InvalidToken');
			return $view;
		}
	
		//$adapter = $this->getServiceLocator()->get('Application\Auth\Adapter');
		$adapter = $this->getAuthenticationAdapter();
		$adapter->setOAuth2Client($instanceProvider);
		$authenticationService = $this->getAuthenticationService();
		$authenticate = $authenticationService->authenticate($adapter); // return Zend\Authentication\Result
        
		$view->setVariable('authenticate', $authenticate);
	
		return $view;
	}
	
	public function logoutAction()
	{
		$authenticationService = $this->getAuthenticationService();
		 
		if(!$authenticationService->hasIdentity())
		{
			$this->returnToHome();
			return;
		}
		 
		$identity = $authenticationService->getIdentity();
	
		$authenticationService->clearIdentity();
		 
		if(array_key_exists('sessionOfProvider', $identity) &&
				"" != $identity["sessionOfProvider"])
		{
			$identity["sessionOfProvider"]->clear();
			 
		}
			
		return $this->getRedirectAfterLogout();
	}

	public function returnToHome()
	{
		return $this->redirect()->toRoute('home');
	}	

	public function getRedirectAfterLogout()
	{
		if (!$this->redirectAfterLogout) {
			$this->setRedirectAfterLogout($this->redirect()->toRoute('home'));
		}
		return $this->redirectAfterLogout;
	}
	
	public function setRedirectAfterLogout(\Zend\Http\Response $redirect)
	{
	
		$this->redirectAfterLogout = $redirect;
		return $this;
	}
	
	protected function getAuthenticationAdapter()
	{
		$serviceLocator = $this->getServiceLocator();
		return $serviceLocator->get('ZendOAuth2\Auth\Adapter');
	}
	
	protected function getAuthenticationService()
	{
		if (!isset($this->authenticationService))
		{
			$serviceLocator = $this->getServiceLocator();
			$this->authenticationService = $serviceLocator->get('Application\Service\AuthenticationService');
		}
	
		return $this->authenticationService;
	}
	
	public function setAuthenticationService($authenticationService)
	{
		$this->authenticationService = $authenticationService;
	}	
	
	protected function getUserService()
	{
		if (!isset($this->userService))
		{
			$serviceLocator = $this->getServiceLocator();
			$this->userService = $serviceLocator->get('User\UserService');
		}
		 
		return $this->userService;
	}	
	
	protected function getOrganizationService()
	{
		if (!isset($this->organizationService))
		{
			$serviceLocator = $this->getServiceLocator();
			$this->organizationService = $serviceLocator->get('Organization\OrganizationService');
		}

		return $this->organizationService;
	}	
}
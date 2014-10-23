<?php

namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class AuthController extends AbstractActionController
{        
	private $authenticationService;
	private $userService;
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
			//$this->getServiceLocator()->get('Zend\Log\Logger')->crit('LOGIN: Error code parameter: '.$this->getRequest()->getQuery('code'));
			$view->setVariable('error', 'Auth.InvalidCode');
		}
	
		if("" === $provider
				|| !array_key_exists($provider, $availableProviderList))
		{
			//$this->getServiceLocator()->get('Zend\Log\Logger')->crit('lOGIN: Error Provider parameter: '.$provider);
			$view->setVariable('error', 'Auth.InvalidProvider');
		}
		 
		$instanceProvider = $availableProviderList[$provider];
		 
		if(!$instanceProvider->getToken($this->request))
		{
			//TODO: $this->getServiceLocator()->get('Zend\Log\Logger')->crit('Auth: InvalidToken');
			$view->setVariable('error', 'Auth.InvalidToken');
		}
	
		$adapter = $this->getServiceLocator()->get('Application\Auth\Adapter');
		$infoLoggedUser = $adapter->getInfoOfProvider($instanceProvider);
		 
		$userService = $this->getUserService();
		
		/* TODO: Evento per la subscribe */
		$user = $userService->subscribeUser($infoLoggedUser);
		
		$adapter->setUserIdentity($user);
		 
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
			$this->userService = $serviceLocator->get('User\Service\UserService');
		}
		 
		return $this->userService;
	}	
}
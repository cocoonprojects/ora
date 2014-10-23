<?php

namespace Auth\Controller;

use Zend\View\Model\JsonModel;
use Zend\Session\Container;

use Zend\View\Model\ViewModel;
use Zend\Mvc\MvcEvent;

use Zend\Mvc\Controller\AbstractActionController;

class LoginController extends AbstractActionController
{    
    
	private $authenticationService;
	private $userService;
			
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
	    	$view->setVariable('error', 'Auth.InvalidToken');
	    }

	    $adapter = $this->getServiceLocator()->get('Auth\Adapter');
	    $infoLoggedUser = $adapter->getInfoOfProvider($instanceProvider);
	    
	    $userService = $this->getUserService();
	    $user = $userService->subscribeUser($infoLoggedUser);
	    
	    $adapter->setUserIdentity($user);
	    
	    $authenticationService = $this->getAuthenticationService();
	    $authenticate = $authenticationService->authenticate($adapter); // return Zend\Authentication\Result
	    	    
	    $view->setVariable('authenticate', $authenticate);
	    	    	    	
	    return $view;	
    }  

    protected function getAuthenticationService()
    {
    	if (!isset($this->authenticationService))
    	{
    		$serviceLocator = $this->getServiceLocator();
    		$this->authenticationService = $serviceLocator->get('Auth\Service\AuthenticationService');
    	}
    
    	return $this->authenticationService;
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
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
			
    public function loginAction()
    {
    	$resultAuthentication = array();
    	
    	$provider = $this->params('id');
    	$provider = ucfirst($provider);
    	
    	$availableProviderList = $this->getServiceLocator()->get('providerInstanceList');
    	
    	if(strlen($this->params('code')) > 10)
    	{
    		// errore 500 - scrivi in log code: $this->getRequest()->getQuery('code') 
    		$this->response->setStatusCode(500);
    		return;
    	} 

    	if("" === $provider 
	    		|| !array_key_exists($provider, $availableProviderList))
	    {
	    	$this->response->setStatusCode(503);
	    	return;		
	    }	
	    	   
	    $instanceProvider = $availableProviderList[$provider];
		
	    if($instanceProvider->getToken($this->request))
	    {	      
	    	$adapter = $this->getServiceLocator()->get('ZendOAuth2\Auth\Adapter');
	    	$adapter->setOAuth2Client($instanceProvider); 
	
	    	$authenticationService = $this->getAuthenticationService();
	    	$authenticate = $authenticationService->authenticate($adapter); // return Zend\Authentication\Result
	    		
	    	$view = new ViewModel(array(
	    			'authenticate' => $authenticate
	    	));
	    	
	    	return $view;	    				
	    }
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
}
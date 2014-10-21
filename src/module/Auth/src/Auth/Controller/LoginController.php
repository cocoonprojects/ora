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
    	$allConfigurationOption = $this->getServiceLocator()->get('Config');
    	$availableProviderList = array();
    	$resultAuthentication = array();
    	
    	$provider = $this->params('id');

    	if(strlen($this->params('code')) > 10)
    	{
    		// errore 500 - scrivi in log code: $this->getRequest()->getQuery('code') 
    		return;
    	} 
    	
    	if(!is_array($allConfigurationOption) || !array_key_exists('zendoauth2', $allConfigurationOption))
    	{
    		// errore
    		return;
    	}


    		$availableProviderList = $allConfigurationOption['zendoauth2'];
    		
	    	if("" != $provider 
	    		&& array_key_exists($provider, $availableProviderList))
	    	{				
	    		$provider = ucfirst($provider);
	    		$instanceProviderName = "ZendOAuth2\\".$provider;
	    		$instanceProvider = $this->getServiceLocator()->get($instanceProviderName);
	    		
	    		if($instanceProvider->getToken($this->request))
	    		{    	
	    			$adapter = $this->getServiceLocator()->get('ZendOAuth2\Auth\Adapter');
	    			$adapter->setOAuth2Client($instanceProvider); 
	
	    			$authenticationService = $this->getAuthenticationService();
	    			$authenticate = $authenticationService->authenticate($adapter); // return Zend\Authentication\Result
	    		
	    			if($authenticate->isValid())
	    			{
	    				$container = new Container("Zend_Auth");
	    				$identity = $authenticationService->getIdentity();
	    				$identity["provider"] = $provider;
	    				
	    				$authenticationService->getStorage()->write($identity);    				
	    				$resultAuthentication['valid'] = true;
	    			}
	    			else
	    			{
	    				switch ($authenticate->getCode()) {
	    					 
	    					case \Zend\Authentication\Result::FAILURE_IDENTITY_NOT_FOUND:
	    						$resultAuthentication['messages'][] = "Identity not found";
	    						break;
	    			
	    					case \Zend\Authentication\Result::FAILURE_CREDENTIAL_INVALID:
	    						$resultAuthentication['messages'][] = "Credential invalid";
	    						break;
	    			
	    					default:
	    						$resultAuthentication['messages'][] = "Internal error";
	    						break;
	    				}
	    			
	    				$resultAuthentication['messages'] = array_merge($login['messages'], $authenticate->getMessages());
	    			}    			
	    		}

    	}    	
    	
        $view = new ViewModel(array(
        		'login' => $resultAuthentication
        ));
                
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
}
<?php

namespace Auth\Controller;

use Zend\View\Model\JsonModel;

use Zend\Mvc\Controller\AbstractActionController;

class LogoutController extends AbstractActionController
{     
	protected $redirectAfterLogout;
	
    public function logoutAction()
    {
    	$authenticationService = $this->getAuthenticationService();
    	
    	if($authenticationService->hasIdentity())
    	{
    		$identity = $authenticationService->getIdentity();
    	
    		$provider = $identity["provider"];
    		 
    		if("" !== $provider)
    		{
    			$authenticationService->clearIdentity();
    			 
    			$provider = ucfirst($provider);
	    		$instanceProviderName = "ZendOAuth2\\".$provider;
	    		$instanceProvider = $this->getServiceLocator()->get($instanceProviderName);
    	
    			if(null !== $instanceProvider)
    			{
    				$session = $instanceProvider->getSessionContainer();
    				$session->getManager()->getStorage()->clear();
    			}    	
    		}
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

    public function getAuthenticationService()
    {
    	if (!isset($this->authenticationService))
    	{
    		$serviceLocator = $this->getServiceLocator();
    		$this->setAuthenticationService($serviceLocator->get('Auth\Service\AuthenticationService'));
    	}
    
    	return $this->authenticationService;
    }    

    public function setAuthenticationService($authenticationService)
    {
    	$this->authenticationService = $authenticationService;
    }
}

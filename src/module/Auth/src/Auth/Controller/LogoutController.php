<?php

namespace Auth\Controller;

use Zend\View\Model\JsonModel;

use Zend\Mvc\Controller\AbstractActionController;

class LogoutController extends AbstractActionController
{     
	protected $redirectAfterLogout;
	protected $instanceProvider;
	
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

<?php

namespace Auth\Controller;

use Zend\View\Model\JsonModel;

use ZendExtension\Mvc\Controller\AbstractHATEOASRestfulController;

class LogoutController extends AbstractHATEOASRestfulController
{     
	protected static $collectionOptions = array ('GET');
	protected static $resourceOptions = array ('GET');
		
	protected $authService;
	protected $redirectAfterLogout;
	
    public function getList()
    {
    	$authenticationService = new \Zend\Authentication\AuthenticationService();
    	
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

    public function getResponseWithHeader()
    {
        $response = $this->getResponse();
        $response->getHeaders()
                 ->addHeaderLine('Access-Control-Allow-Origin','*')
                 ->addHeaderLine('Access-Control-Allow-Methods','GET');
        
        return $response;
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

    protected function getCollectionOptions() {
        return self::$collectionOptions;
    }
    
    protected function getResourceOptions() {
        return self::$resourceOptions;
    }    
}

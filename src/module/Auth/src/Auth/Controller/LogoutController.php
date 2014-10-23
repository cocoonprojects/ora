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
    	$authService = $this->getAuthService();
    	$logout = $authService->clearIdentity();

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
    
    public function getAuthService()
    {
    	if (!$this->authService) {
    		$this->setAuthService($this->getServiceLocator()->get('\Auth\Service\AuthService'));
    	}
    	return $this->authService;
    }
    
    public function setAuthService(\Auth\Service\AuthService $authService)
    {
        $this->authService = $authService;
        return $this;
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

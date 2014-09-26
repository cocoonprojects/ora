<?php

namespace Auth\Controller;

use Zend\Mvc\Controller\AbstractRestfulController;
use Zend\View\Model\JsonModel;
use Zend\Session\Container;

class LoginController extends AbstractRestfulController
{    
    /***
     * Redirect Uri
     */
	protected $authService;

    public function get($id)
    {
        $authService = $this->getAuthService();
        $login = $authService->loginToProvider($id);
        
      
        if($login['valid'])
        {
        	echo "ok";
        }
        else
        {
        	// Errore
        	//var_dump($login);
        
        }
        
        //return $this->redirect()->toRoute('home');
    }

    public function getList()
    {
    	echo "getList";
    	//return $this->redirect()->toRoute('home');
    }
    
    public function getResponseWithHeader()
    {
        $response = $this->getResponse();
        $response->getHeaders()
                 ->addHeaderLine('Access-Control-Allow-Origin','*')
                 ->addHeaderLine('Access-Control-Allow-Methods','GET');
        
        return $response;
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
  
}

<?php

namespace Auth\Controller;

use Zend\View\Model\JsonModel;
use Zend\Session\Container;

use Zend\View\Model\ViewModel;
use Zend\Mvc\MvcEvent;

use ZendExtension\Mvc\Controller\AbstractHATEOASRestfulController;

class LoginController extends AbstractHATEOASRestfulController
{    

    protected static $collectionOptions = array ('GET');
    protected static $resourceOptions = array ('GET');
    
    protected $authService;
    
    public function get($id)
    {
        $authService = $this->getAuthService();
        $login = $authService->loginToProvider($id);
        
        /**
         * $login['valid'] = boolean
         * $login['messages'] = array()
         */
        
        $view = new ViewModel(array(
        		'login' => $login
        ));
        
        $view->setTemplate("login/login");
        
        return $view;        
        //return new JsonModel(array('login' => $login));
    }

    public function getList()
    {
        return $this->redirect()->toRoute('home');
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

    protected function getCollectionOptions() {
    	return self::$collectionOptions;
    }
    
    protected function getResourceOptions() {
    	return self::$resourceOptions;        
    }       
  
}
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
    
    public function get($id)
    {
    	$allConfigurationOption = $this->getServiceLocator()->get('Config');
    	$availableProviderList = array();
    	$resultAuthentication = array();
    	
    	if(is_array($allConfigurationOption) && array_key_exists('zendoauth2', $allConfigurationOption))
    	{
    		$availableProviderList = $allConfigurationOption['zendoauth2'];
    	}
    	
    	if("" != $id 
    		&& array_key_exists($id, $availableProviderList) 
    		&& strlen($this->getRequest()->getQuery('code')) > 10)
    	{
    		$provider = ucfirst($id);
    		$instanceProviderName = "ZendOAuth2\\".$provider;
    		$instanceProvider = $this->getServiceLocator()->get($instanceProviderName);
    		
    		
    		if($instanceProvider->getToken($this->getRequest()))
    		{    			
    			$adapter = $this->getServiceLocator()->get('ZendOAuth2\Auth\Adapter');
    			$adapter->setOAuth2Client($instanceProvider); 

    			$authenticationService = new \Zend\Authentication\AuthenticationService();
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
    	else
    	{
    		$resultAuthentication['messages'][] = "Provider adapter is not valid";
    	}    	
    	
        $view = new ViewModel(array(
        		'login' => $resultAuthentication
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

    protected function getCollectionOptions() {
    	return self::$collectionOptions;
    }
    
    protected function getResourceOptions() {
    	return self::$resourceOptions;        
    }       

}
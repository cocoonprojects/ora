<?php

namespace Auth\Service;

use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\ServiceManager\ServiceManagerAwareInterface;
use Zend\ServiceManager\ServiceManager;
use Zend\Session\Container;
/**
 * Service for authentication
 */

class AuthService implements ServiceManagerAwareInterface 
{
    private $serviceManager;
    private $authenticationService;
    private $keyOptionInConfiguration = 'zendoauth2';
    private $instanceProviderNamePrefix = "ZendOAuth2\\";
    private $adapterForAuthentication  = "ZendOAuth2\Auth\Adapter";
    private $namespaceOfContainerForSession = "Zend_Auth";
    private $errorOnProvider;
    
     /**
      * Retrieve service manager instance
      *
      * @return ServiceManager
      */
     public function getServiceManager()
     {
          if ($this->serviceManager instanceof ServiceManager) 
          	return $this->serviceManager;
          else
          	return null;
     }

     /**
      * Set service manager instance
      *
      * @param ServiceManager $serviceManager
      * @return serviceManager
      */
     public function setServiceManager(ServiceManager $serviceManager)
     {
         $this->serviceManager = $serviceManager;
         return $this;
     }       

    /**
    * Get list of available provider for login
    * 
    * @return array
    */
    public function availableProvider()
    {
        $availableProviderList = array();

        $configurationOption = $this->configurationOption();

        if(is_array($configurationOption))
        {
	        if(array_key_exists($this->keyOptionInConfiguration, $configurationOption))
	        {
	           $availableProviderList = $configurationOption[$this->keyOptionInConfiguration];
	        }
        }
        
        return $availableProviderList;
    }
    
    /**
     * Get all configuration option of application
     *
     * @return array
     */
    
    public function configurationOption()
    {
    	$config = null;
    	 
    	if(null !== $this->getServiceManager())
    		$config = $this->allConfigFromServiceManager();
    	 
    	return $config;
    }
    
    public function allConfigFromServiceManager()
    {
    	return ($this->getServiceManager()->get('Config')) ? $this->getServiceManager()->get('Config') : null;
    }    

    /**
     * Get list of available provider with url-redirect for generate authorization code for login in provider
     *
     * @return array
     */   
     
    public function listOfUrlRedirectForGenerateAuthorizationCodeForAvailablesProvider()
    {
    	$urlList = array();
    	$avaiblesProvider = $this->availableProvider();
    	
    	foreach($avaiblesProvider as $provider => $providerOptions)
    	{
    		$provider = ucfirst($provider);   	
    		$instanceProvider = $this->getInstanceOfProvider($provider);
    	
    		if(null != $instanceProvider)
    			$urlList[$provider] =  $this->urlRedirectForGenerateAuthorizationCodeForProvider($instanceProvider);    	
    	}
    	    	
    	return $urlList;
    }
    
    /**
     *  Get URL Redirect for generate authorization code for login in specific provider 
     *
     * @return string (url)
     */
        
    public function urlRedirectForGenerateAuthorizationCodeForProvider($instanceProvider)
    {
    	$url = null;
    	 
    	if(is_object($instanceProvider) && method_exists($instanceProvider, 'getUrl'))
    	{
    		$url = $instanceProvider->getUrl();
    	}
    	 
    	return $url;
    }     
    

    /**
     *  Check if at least one availables provider have token (user is logged)
     *
     * @return boolean
     */
        
    public function atLeastOneAvailablesProviderHaveToken()
    {
    	$avaiblesProvider = $this->availableProvider();
    	$haveToken = false;
	    
	    foreach($avaiblesProvider as $provider => $providerOptions)
	    {
	    	$provider = ucfirst($provider);
	    			    		    	
	    	if($this->haveTokenInRequestForProvider($provider))
	    	{
	    		$haveToken = true;
	    	}    		
	    }    	
    	
    	return $haveToken;
    }
    
    /**
     *  Check if selected provider have token
     *
     *	@param string provider
     *
     * 	@return boolean
     */
    public function haveTokenInRequestForProvider($provider)
    {
    	$token = false;
    	     	
    	$instanceProvider = $this->getInstanceOfProvider($provider);
    	$request = $this->getRequest();
    	 
    	if(null !== $instanceProvider && null !== $request)
    		$token = $instanceProvider->getToken($request);
    	 
    	if(!$token)
    	{
    		$this->setErrorOnProvider($instanceProvider->getError());
    	}
    	 
    	return $token;
    }    
        
    public function loginToProvider($provider)
    {
    	$avaiablesProvider = $this->availableProvider();
    	
    	$loginResult['valid'] = false;
    	$loginResult['messages'] = array();
    	
    	if ("" != $provider && array_key_exists($provider, $avaiablesProvider))
    	{
    		$provider = ucfirst($provider);
    		
    		if ($this->verifyLengthOfCodeParameter(10))
    		{
    			if($this->haveTokenInRequestForProvider($provider))
    			{
    				$authenticate = $this->authenticateToProvider($provider);
    				 
    				if ($authenticate['valid']) {

    					$this->saveAuthenticationOfUserInSession($provider);
    					$loginResult['valid'] = true;
    					 
    				} else {
    					$loginResult['messages'] = $authenticate['messages'];
    				}
    		
    			} else {
    					$loginResult['messages'] = $this->getErrorOnProvider();

    			}
    		}
    		else
    		{
    			$loginResult['messages'] = "Error Login";
    		}    		
    	}
    	else
    	{
    		$loginResult['messages'] = "Provider is not enabled";
    	}
    	
    	return $loginResult;
    }

    
    
    /**
     *  Authenticate User on provider
     *
     *	@param string provider
     *
     * 	@return array('valid' => boolean, 'messages' => array)
     */    
    
    public function authenticateToProvider($provider)
    {
    	$validAuthenticate['valid'] = false;
    	$validAuthenticate['messages'] = array();
    
    	if("" !== $provider)
    	{
	    	$adapter = 	$this->getAdapter($provider); 
	    	
	    	if(null !== $adapter)   
	    		$validAuthenticate = $this->authenticate($adapter);
	    	else
	    		$validAuthenticate['messages'][] = "Provider adapter is not valid";
    	}
    	else {
    		$validAuthenticate['messages'][] = "Provider empty";
    	}

    	return $validAuthenticate;    	
    }
    
    /**
     * Get Adapter for Auth
     * 
     * @param string $provider
     * @return \ZendOAuth2\Authentication\Adapter\ZendOAuth2
     */
    public function getAdapter($provider)
    {
    	$adapter = null;
    	
    	if("" !== $provider)
    	{
	    	$instanceProvider = $this->getInstanceOfProvider($provider);
	    	$adapter = $this->getServiceManager()->get($this->adapterForAuthentication);
	    	$adapter->setOAuth2Client($instanceProvider);
    	}
    	
    	return $adapter;
    }
    
    /**
     * Authenticate with adapter
     * 
     * @param \ZendOAuth2\Authentication\Adapter\ZendOAuth2 $adapter
     * @return Ambigous <boolean, string>
     */
    public function authenticate(\ZendOAuth2\Authentication\Adapter\ZendOAuth2 $adapter)
    {
    	$resultAuthenticate['valid'] = false;
    	$resultAuthenticate['messages'] = array();
    	    	
    	$auth = $this->getAuthenticationService();

    	if(null !== $adapter)
    	{
	    	$authWithAdapter = $auth->authenticate($adapter); // return Zend\Authentication\Result  
	    	
	    	if($authWithAdapter->isValid())
	    	{
	    		$resultAuthenticate['valid'] = true;
	    	}
	    	else
	    	{
	    		switch ($authWithAdapter->getCode()) {
	    			 
	    			case \Zend\Authentication\Result::FAILURE_IDENTITY_NOT_FOUND:
	    				$resultAuthenticate['messages'][] = "Identity not found";
	    				break;
	    		   
	    			case \Zend\Authentication\Result::FAILURE_CREDENTIAL_INVALID:
	    				$resultAuthenticate['messages'][] = "Credential invalid";
	    				break;
	    				 
	    			default:
	    				$resultAuthenticate['messages'][] = "Internal error";
	    				break;
	    		}
	    	
	    		$resultAuthenticate['messages'] = array_merge($resultAuthenticate['messages'], $authWithAdapter->getMessages());
	    	}
    	}
    	else 
    	{
    		$resultAuthenticate['messages'][] = "Provider adapter is not valid";
    	}
    	
    	return $resultAuthenticate;
    }
    /**
     *  Save in Session authenticated user
     *
     *	@param string provider
     *
     * 	@return 
     */
        
    public function saveAuthenticationOfUserInSession($provider)
    {
    	$container = new Container($this->namespaceOfContainerForSession);
    	
    	$auth = $this->getAuthenticationService();
    	
    	$identity = $auth->getIdentity();
    	$identity["provider"] = $provider;
    	 
    	$auth->getStorage()->write($identity);
    }    

    /**
     *  Destroy Identity
     *
     * @return boolean
     */    
    public function clearIdentity()
    {
    	$destroy = false;
    	
    	$auth = $this->getAuthenticationService();
    	
    	if($auth->hasIdentity())
    	{
    		$identity = $auth->getIdentity();

    		$provider = $identity["provider"];
    	
    		if("" !== $provider)
    		{
    			$auth->clearIdentity();
    			
    			$instanceProvider = $this->getInstanceOfProvider($provider); 

    			if(null !== $instanceProvider)
    			{
	    			$session = $instanceProvider->getSessionContainer();
	    			$session->getManager()->getStorage()->clear();
    			}
    				
    			$destroy = true;
    		}
    	}

    	return $destroy;
    }
        
    /**
     *  Get instance of Provider selected
     *
     * @return \ZendOAuth2\AbstractOAuth2Client (instance of)
     */
    
    public function getInstanceOfProvider($provider)
    {
    	$instanceProvider = null;
    	
    	if("" != $provider)
    	{
	    	$instanceProviderName = $this->instanceProviderNamePrefix.$provider;
	    
	    	$instanceProvider = (null !== $this->getServiceManager()) ? $this->getServiceManager()->get($instanceProviderName) : null;
	    	    	
    	}
    	
    	return $instanceProvider;
    }
    
    public function getAuthenticationService()
    {
    	if (!$this->authenticationService) {
    		$this->authenticationService = new \Zend\Authentication\AuthenticationService();
    	}
    	return $this->authenticationService;
    }
        
    public function verifyLengthOfCodeParameter($minLength)
    {
    	return (strlen($this->getRequest()->getQuery('code')) > $minLength) ? true : false;
    }
        
	public function setErrorOnProvider($errorOnProvider)
	{
		$this->errorOnProvider = $errorOnProvider;
	}
	
	public function getErrorOnProvider()
	{
		return $this->errorOnProvider;
	}

	/**
	 * Ger Request Object
	 *
	 * @return Zend\Http\PhpEnvironment\Request
	 */
	protected function getRequest()
	{
		return (null !== $this->getServiceManager()) ? $this->getServiceManager()->get('Request') : null;
	}
  
}
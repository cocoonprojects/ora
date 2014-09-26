<?php

namespace Auth\Service;

use Auth\Service\AuthService as Service;
use Zend\ServiceManager\ServiceManager;  
use PHPUnit_Framework_TestCase;
use Zend\Mvc\Application;
use ZendOAuth2\AbstractOAuth2Client;

class AuthServiceTest extends PHPUnit_Framework_TestCase
{
    protected $service;
    protected $configurationOption = array();
    protected $availableProvider = array();
    
    protected function setUp()
    {     
        $bootstrap = Application::init(include 'tests/unit/test.config.php');

        $this->service = new Service;
        
        $this->avaiblesProvider = array( 
								    	'google' => array(), 
								    	'linkedin' => array () 
    									);
        
        $this->configurationOption['zendoauth2'] = $this->avaiblesProvider;        
    }

    public function testAvailableProviderCorrect()
    {
    	$mock = $this->getMockBuilder('Auth\Service\AuthService')
    							   ->setMethods(array('configurationOption'))
    							   ->getMock();
    	
    	$mock->method('configurationOption')
    	     ->willReturn($this->configurationOption);
    	
    	$availableProvider = $mock->availableProvider();
    	
    	$this->assertEquals($availableProvider, $this->avaiblesProvider);
    } 
    
    public function testAvailableProviderkeyOptionInConfigurationNotFound()
    {
    	$returnConfigurationOption['other_config'] = array();
    
    	$mock = $this->getMockBuilder('Auth\Service\AuthService')
				    	->setMethods(array('configurationOption'))
				    	->getMock();
    	 
    	$mock->method('configurationOption')
    		->willReturn($returnConfigurationOption);
    	 
    	$availableProvider = $mock->availableProvider();
    	 
    	$this->assertEquals($availableProvider, array());
    }    
    
    public function testAvailableProviderConfigurationOptionFail()
    {
    	$returnConfigurationOption = null;
    
    	$mock = $this->getMockBuilder('Auth\Service\AuthService')
				    	->setMethods(array('configurationOption'))
				    	->getMock();
    
    	$mock->method('configurationOption')
    		 ->willReturn($returnConfigurationOption);
    
    	$availableProvider = $mock->availableProvider();
    
    	$this->assertEquals($availableProvider, array());
    }

    public function testConfigurationOptionCorrect()
    {
    	$listConfig = array('configurationOption' => '...');
    	
    	$mock = $this->getMockBuilder('Auth\Service\AuthService')
			    	->setMethods(array('getServiceManager', 'allConfigFromServiceManager'))
			    	->getMock();
    	    	
    	$mock->method('getServiceManager')->willReturn('serviceManagerInstance');
    	$mock->method('allConfigFromServiceManager')->willReturn($listConfig);

    	$configurationOptionList = $mock->configurationOption();
    	$this->assertEquals($configurationOptionList, $listConfig);
    }
    
    public function testConfigurationOptionServiceManagerFail()
    {    	 
    	$mock = $this->getMockBuilder('Auth\Service\AuthService')
			    	->setMethods(array('getServiceManager', 'allConfigFromServiceManager'))
			    	->getMock();
    
    	$mock->method('getServiceManager')->willReturn(null);
    	$mock->method('allConfigFromServiceManager')->willReturn(null);
    
    	$configurationOptionList = $mock->configurationOption();
    	$this->assertEquals($configurationOptionList, null);
    }    
    
    public function testListOfUrlRedirectForGenerateAuthorizationCodeForAvailablesProvider()
    {
    	$listExpected = array(
    			'Google' => 'urlRedirectForGenerateAuthorizationCode',
    			'Linkedin' => 'urlRedirectForGenerateAuthorizationCode',
    	);
    	
    	$mock = $this->getMockBuilder('Auth\Service\AuthService')
			    	->setMethods(array('availableProvider', 'getInstanceOfProvider', 'urlRedirectForGenerateAuthorizationCodeForProvider'))
			    	->getMock();
    	 
    	$mock->method('availableProvider')
    				->willReturn($this->avaiblesProvider);
    	
    	$mock->method('getInstanceOfProvider')
    				->willReturn('serviceProviderInstance');    	
    	
    	$mock->method('urlRedirectForGenerateAuthorizationCodeForProvider')
    		->willReturn('urlRedirectForGenerateAuthorizationCode');    	
    	
    	$urlList = $mock->listOfUrlRedirectForGenerateAuthorizationCodeForAvailablesProvider();
    	
    	$this->assertEquals($urlList, $listExpected);
    }
    
    public function testUrlRedirectForGenerateAuthorizationCodeForProvider()
    {    
    	$urlForLoginOfProvider = "urlRedirectForGenerateAuthorizationCodeForSpecificProvider";
    	
    	$instanceProvider = $this->getMockForAbstractClass('ZendOAuth2\AbstractOAuth2Client');
		$instanceProvider->expects($this->any())
		    	->method('getUrl')
		    	->willReturn($urlForLoginOfProvider);
    	
    	$url = $this->service->urlRedirectForGenerateAuthorizationCodeForProvider($instanceProvider);    	    

    	$this->assertEquals($url, $urlForLoginOfProvider);
    }
    
    public function testAtLeastOneAvailablesProviderHaveToken()
    {   	    	    
    	$mock = $this->getMockBuilder('Auth\Service\AuthService')
				    	->setMethods(array('availableProvider', 'haveTokenInRequestForProvider'))
				    	->getMock();
    	
    	$mock->method('availableProvider')
    					->willReturn($this->avaiblesProvider);
    	    	
    	$mock->method('haveTokenInRequestForProvider')
    					->willReturn(true);    	

    	$haveToken = $mock->atLeastOneAvailablesProviderHaveToken();
    	
    	$this->assertEquals($haveToken, true);
    }
    
    public function testNoOneProviderHaveToken()
    {    
    	$mock = $this->getMockBuilder('Auth\Service\AuthService')
			    	->setMethods(array('availableProvider', 'getRequest', 'haveTokenInRequestForProvider'))
			    	->getMock();
    	 
    	$mock->method('availableProvider')
    				->willReturn($this->avaiblesProvider);
    	 
    	$mock->method('getRequest')
    				->willReturn(new \stdClass()); // getRequest ritorna un'istanza di ZendRequest - per il test passo un semplice oggettto
    
    	$mock->method('haveTokenInRequestForProvider')
    				->willReturn(false);
    
    	$haveToken = $mock->atLeastOneAvailablesProviderHaveToken();
    	 
    	$this->assertEquals($haveToken, false);
    }    
    
    public function testAuthenticateToProvideronSuccess()
    {
    	$provider = "Linkedin";

    	$resultAuthenticate['valid'] = true;
    	$resultAuthenticate['messages'] = array();
    	
    	$mock = $this->getMockBuilder('Auth\Service\AuthService')
    					->setMethods(array('getAdapter', 'authenticate'))
    					->getMock(); 

    	$mock->method('getAdapter')
    			->will($this->returnCallback(array($this, 'getAdapterAuthenticationInstance')));
    	
    	$mock->method('authenticate')
    			->willReturn($resultAuthenticate); 

    	$authenticateToProvider = $mock->authenticateToProvider($provider);
    	
    	$this->assertEquals($authenticateToProvider, $resultAuthenticate);
    }

    public function testAuthenticateToProviderFail()
    {
    	$provider = "Linkedin";
    
    	$resultAuthenticate['valid'] = false;
    	$resultAuthenticate['messages'][] = "Identity not found";
    	 
    	$mock = $this->getMockBuilder('Auth\Service\AuthService')
			    	->setMethods(array('getAdapter', 'authenticate'))
			    	->getMock();
    
    	$mock->method('getAdapter')
    			->will($this->returnCallback(array($this, 'getAdapterAuthenticationInstance')));
    
    	$mock->method('authenticate')
    				->willReturn($resultAuthenticate);
    
    	$authenticateToProvider = $mock->authenticateToProvider($provider);
    	 
    	$this->assertEquals($authenticateToProvider, $resultAuthenticate);
    }    
    
    public function testAuthenticateToProviderProviderEmpty()
    {
    	$provider = "";
    
    	$resultAuthenticate['valid'] = false;
    	$resultAuthenticate['messages'][] = "Provider empty";
    	 
    	$mock = $this->getMockBuilder('Auth\Service\AuthService')
			    	->setMethods(array('getAdapter', 'authenticate'))
			    	->getMock();
    
    	$mock->method('getAdapter')
    				->willReturn(null);
    
    	$mock->method('authenticate')
    				->willReturn(null);
    
    	$authenticateToProvider = $mock->authenticateToProvider($provider);
    	 
    	$this->assertEquals($authenticateToProvider, $resultAuthenticate);
    }  

    public function testAuthenticateSuccess()
    {
    	$case = "success";

    	$expect['valid'] = true;
    	$expect['messages'] = array();
    	
    	$mock = $this->getMockBuilder('Auth\Service\AuthService')
			    	->setMethods(array('getAuthenticationService'))
			    	->getMock();  
    	  	
    	$mock->method('getAuthenticationService')
    				->willReturn($this->getMockedAuthenticationService($case));
    	    	
    	$adapter = $this->getAdapterAuthenticationInstance();
    	$authenticate = $mock->authenticate($adapter);
    	
    	$this->assertEquals($authenticate, $expect);
    }
    
    public function testAuthenticateIdentityNotNound()
    {
    	$case = "Identitynotfound";
    
    	$expect['valid'] = false;
    	$expect['messages'][] = "Identity not found";
    	
    	$mock = $this->getMockBuilder('Auth\Service\AuthService')
			    	->setMethods(array('getAuthenticationService'))
			    	->getMock();
    
    	$mock->method('getAuthenticationService')
    				->willReturn($this->getMockedAuthenticationService($case));
    
    	$adapter = $this->getAdapterAuthenticationInstance();
    	$authenticate = $mock->authenticate($adapter);
    	 
    	$this->assertEquals($authenticate, $expect);
    }   

    public function testAuthenticateInvalidCredential()
    {
    	$case = "invalidcredential";
    
    	$expect['valid'] = false;
    	$expect['messages'][] = "Credential invalid";
    	 
    	$mock = $this->getMockBuilder('Auth\Service\AuthService')
    	->setMethods(array('getAuthenticationService'))
    	->getMock();
    
    	$mock->method('getAuthenticationService')
    	->willReturn($this->getMockedAuthenticationService($case));
    
    	$adapter = $this->getAdapterAuthenticationInstance();
    	$authenticate = $mock->authenticate($adapter);
    
    	$this->assertEquals($authenticate, $expect);
    }   

    public function testAuthenticateInternalError()
    {
    	$case = "internalerror";
    
    	$expect['valid'] = false;
    	$expect['messages'][] = "Internal error";
    	$expect['messages'][] = "Other Message error from adapter";
    	
    	$mock = $this->getMockBuilder('Auth\Service\AuthService')
    	->setMethods(array('getAuthenticationService'))
    	->getMock();
    
    	$mock->method('getAuthenticationService')
    	->willReturn($this->getMockedAuthenticationService($case));
    
    	$adapter = $this->getAdapterAuthenticationInstance();
    	$authenticate = $mock->authenticate($adapter);
    
    	$this->assertEquals($authenticate, $expect);
    }    
        
    public function getMockedAuthenticationService($case)
    {    	
    	$mockAuthenticationService = $this->getMockBuilder('\Zend\Authentication\AuthenticationService')
									      ->setMethods(array('authenticate'))
										  ->getMock();
    	
    	$mockAuthenticationService->method('authenticate')
    					->willReturn($this->getMockAuthenticationResult($case));
    	    	
    	return $mockAuthenticationService;
    }
    
    public function getMockAuthenticationResult($case)
    {
    	$caseAuthentication["success"] = array(
    			'isValid' => true,
    			'getCode' => \Zend\Authentication\Result::SUCCESS,
    			'getMessages' => array()
    	);
    	
    	$caseAuthentication["invalidcredential"] = array(
    			'isValid' => false,
    			'getCode' => \Zend\Authentication\Result::FAILURE_CREDENTIAL_INVALID,
    			'getMessages' => array()
    	);  

    	$caseAuthentication["Identitynotfound"] = array(
    			'isValid' => false,
    			'getCode' => \Zend\Authentication\Result::FAILURE_IDENTITY_NOT_FOUND,
    			'getMessages' => array()
    	);    	  	
    	
    	$caseAuthentication["internalerror"] = array(
    			'isValid' => false,
    			'getCode' => null,
    			'getMessages' => array("Other Message error from adapter")
    	);    	

    	$mockAuthenticationResult = $this->getMockBuilder('Zend\Authentication\Result')
    										->disableOriginalConstructor()
									    	->setMethods(array('isValid', 'getCode', 'getMessages'))
									    	->getMock();
    	
    	$mockAuthenticationResult->method('isValid')
    							 ->will($this->returnValue($caseAuthentication[$case]['isValid']));    

    	$mockAuthenticationResult->method('getCode')
    							 ->will($this->returnValue($caseAuthentication[$case]['getCode']));
    	
    	$mockAuthenticationResult->method('getMessages')
    							->will($this->returnValue($caseAuthentication[$case]['getMessages']));    	
    	
    	return $mockAuthenticationResult;
    }
    
    public function getAdapterAuthenticationInstance()
    {
    	return new \ZendOAuth2\Authentication\Adapter\ZendOAuth2;
    }
    
}
<?php

namespace Auth\Controller;

use Auth\Controller\LoginController;
use Zend\Http\Request;
use Zend\Http\Response;
use Zend\Mvc\MvcEvent;
use Zend\Mvc\Router\RouteMatch;
use PHPUnit_Framework_TestCase;
use Zend\Mvc\Router\Http\TreeRouteStack as HttpRouter;
use Zend\Mvc\Application;
use \Auth\Service\AuthService as AuthService;


class LoginControllerTest extends \PHPUnit_Framework_TestCase
{
    protected $controller;
    protected $request;
    protected $response;
    protected $routeMatch;
    protected $event;

    protected function setUp()
    {  		
        $bootstrap = Application::init(include 'tests/unit/test.config.php');
        $this->controller = new LoginController();
        $this->request = new Request();
        $this->routeMatch = new RouteMatch(array('controller' => 'login'));
        $this->event = $bootstrap->getMvcEvent();
        
        $this->event->setRouteMatch($this->routeMatch);
        $this->controller->setEvent($this->event);
        $this->controller->setEventManager($bootstrap->getEventManager());
        $this->controller->setServiceLocator($bootstrap->getServiceManager());
       
    }
    
    public function testLoginWithCorrectProvider()
    {
    	$this->request->setMethod('GET');
    	$this->routeMatch->setParam('id', 'google');
    	
    	$authService = $this->getMock('\Auth\Service\AuthService');
    	    	
    	$authService->method('loginToProvider')
    				->will($this->returnValue(array( 
										    	'valid' => true, 
										    	'messages' => array () 
		    									)));     				 
    	    	
    	$this->controller->setAuthService($authService);    
    	
    	$result = $this->controller->dispatch($this->request);
    	    	
    	$response = $this->controller->getResponse();

    	$responseJSON = $result->getVariables();
    	
    	$this->assertEquals(200, $response->getStatusCode());
    	$this->assertInstanceOf('Zend\View\Model\ViewModel', $result);  
    	
    	$this->assertTrue($responseJSON["login"]["valid"]);	  	
    	$this->assertEquals(array(), $responseJSON["login"]["messages"]);
    }
    
    public function testLoginWithErrorMessagesProvider()
    {
    	$this->request->setMethod('GET');
    	$this->routeMatch->setParam('id', 'noprovider');
    	 
    	$errorMessages = array ('Provider is not enabled');
    	
    	$authService = $this->getMock('\Auth\Service\AuthService');
    
    	$authService->method('loginToProvider')
			    	->will($this->returnValue(array(
			    			'valid' => false,
			    			'messages' => $errorMessages
			    	)));
    
    	$this->controller->setAuthService($authService);
    	 
    	$result = $this->controller->dispatch($this->request);
    
    	$response = $this->controller->getResponse();
    
    	$responseJSON = $result->getVariables();
    	 
    	$this->assertEquals(200, $response->getStatusCode());

    	$this->assertInstanceOf('Zend\View\Model\ViewModel', $result);
    	
    	$this->assertFalse($responseJSON["login"]["valid"]);
    	$this->assertEquals($errorMessages, $responseJSON["login"]["messages"]);
    }    
    
    
}
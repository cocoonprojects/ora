<?php

namespace Auth\Controller;

use Auth\Controller\LogoutController;
use Zend\Http\Request;
use Zend\Http\Response;
use Zend\Mvc\MvcEvent;
use Zend\Mvc\Router\RouteMatch;
use PHPUnit_Framework_TestCase;
use Zend\Mvc\Router\Http\TreeRouteStack as HttpRouter;
use Zend\Mvc\Application;
use \Auth\Service\AuthService as AuthService;


class LogoutControllerTest extends \PHPUnit_Framework_TestCase
{
    protected $controller;
    protected $request;
    protected $response;
    protected $routeMatch;
    protected $event;

    protected function setUp()
    {  		
        $bootstrap = Application::init(include 'tests/unit/test.config.php');
        $this->controller = new LogoutController();
        $this->request = new Request();
        $this->routeMatch = new RouteMatch(array('controller' => 'logout'));
        $this->event = $bootstrap->getMvcEvent();
        
        $this->event->setRouteMatch($this->routeMatch);
        $this->controller->setEvent($this->event);
        $this->controller->setEventManager($bootstrap->getEventManager());
       
    }
    
    public function testLogout()
    {
    	$this->request->setMethod('GET');
    	    	
    	$redirectAfterLogout = $this->getMock('\Zend\Http\Response');

    	$this->controller->setRedirectAfterLogout($redirectAfterLogout);
    	
    	$result = $this->controller->dispatch($this->request);
    	$response = $this->controller->getResponse();
    	
    	$this->assertEquals(200, $response->getStatusCode());
 

    }
}


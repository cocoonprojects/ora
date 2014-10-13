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
    }
    
    public function testLoginWithCorrectProvider()
    {
    	$this->request->setMethod('GET');
    	$this->routeMatch->setParam('id', 'google');   

    }
    
}
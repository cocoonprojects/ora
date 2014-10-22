<?php

namespace Kanbanize\Controller;

use Zend\Mvc\Application;
use Zend\Http\Request;
use Zend\Mvc\Router\RouteMatch;
use Zend\Mvc\Router\SimpleRouteStack;


class KanbanizeControllerTest extends \PHPUnit_Framework_TestCase {
	protected $controller;
	protected $request;
	protected $response;
	protected $routeMatch;
	protected $event;
	
	protected $router;
	
	protected function setUp() {
		$bootstrap = Application::init ( include 'tests/unit/test.config.php' );
		$this->controller = new KanbanizeController ();
		$route = new SimpleRouteStack();
	}
}
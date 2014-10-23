<?php

namespace Kanbanize\Controller;

use Kanbanize\Controller\KanbanizeController;
use Zend\Http\Request;
use Zend\Http\Response;
use Zend\Mvc\MvcEvent;
use Zend\Mvc\Router\RouteMatch;
use PHPUnit_Framework_TestCase;
use Zend\Mvc\Router\Http\TreeRouteStack as HttpRouter;
use Zend\Mvc\Application;
use Zend\Http\Client;
use Zend\ServiceManager\ServiceManager;
use Kanbanize\Service\KanbanizeServiceFactory;
use DoctrineORMModule\Service\EntityManagerFactory;
use Ora\Kanbanize\KanbanizeTask;

class KanbanizeControllerTest extends \PHPUnit_Framework_TestCase {
	
	protected $serviceManager;
	
	/**
	 *
	 * @var \Ora\Kanbanize\KanbanizeService
	 */
	protected $kanbanizeService;
	protected $controller;
	protected $request;
	protected $response;
	protected $routeMatch;
	protected $event;
	
	protected function setUp() {
		$bootstrap = Application::init ( include ('tests/unit/test.config.php') );
		$this->serviceManager = $bootstrap->getServiceManager ();
		$this->controller = $this->getMock ( 'Kanbanize\Controller\KanbanizeController', array (
				'getKanbanizeService' 
		) );
		$this->request = new Request ();
		$this->routeMatch = new RouteMatch ( array (
				'controller' => 'kanbanize' 
		) );
		$this->event = new MvcEvent ();
		$config = $this->serviceManager->get ( 'Config' );
		$routerConfig = isset ( $config ['router'] ) ? $config ['router'] : array ();
		$router = HttpRouter::factory ( $routerConfig );
		$this->event->setRouter ( $router );
		$this->event->setRouteMatch ( $this->routeMatch );
		$this->controller->setEvent ( $this->event );
		$this->controller->setServiceLocator ( $this->serviceManager );
		
		$this->kanbanizeService = $this->getMockForAbstractClass ( '\Ora\Kanbanize\KanbanizeService', array (
				'moveTask',
				'isAcceptable',
				'canBeMovedBackToOngoing'
		) );
		
		$this->controller->expects ( $this->any () )->method ( 'getKanbanizeService' )->will ( $this->returnCallback ( array (
				$this,
				'getMockedKanbanizeService' 
		) ) );
	}
	
	public function testMoveSuccessfullyDone() {
		$this->kanbanizeService->expects ( $this->any () )->method ( 'moveTask' )->will ( $this->returnCallback ( array (
				$this,
				'getCorrectResult' 
		) ) );
		$this->kanbanizeService->expects ( $this->any () )->method ( 'isAcceptable' )->will ( $this->returnCallback ( array (
				$this,
				'isReallyAcceptable' 
		) ) );
		
		$this->routeMatch->setParam ( 'id', $this->getTaskId () );
		$this->request->setMethod ( Request::METHOD_PUT );
		$this->request->getHeaders ()->addHeaderLine ( 'Content-Type', 'application/json' );
		$this->request->setContent ( json_encode ( array (
				'boardid' => 3,
				'action' => 'accept' 
		) ) );
		
		$result = $this->controller->dispatch ( $this->request );
		$response = $this->controller->getResponse ();
		
		$this->assertEquals ( 200, $response->getStatusCode () );
	}
	
	public function testMoveOfNotAcceptableTask() {
		$this->kanbanizeService->expects ( $this->any () )->method ( 'moveTask' )->will ( $this->returnCallback ( array (
				$this,
				'getCorrectResult' 
		) ) );
		$this->kanbanizeService->expects ( $this->any () )->method ( 'isAcceptable' )->will ( $this->returnCallback ( array (
				$this,
				'isNotAcceptable' 
		) ) );
		
		$this->routeMatch->setParam ( 'id', $this->getTaskId () );
		$this->request->setMethod ( Request::METHOD_PUT );
		$this->request->getHeaders ()->addHeaderLine ( 'Content-Type', 'application/json' );
		$this->request->setContent ( json_encode ( array (
				'boardid' => 3,
				'action' => 'accept' 
		) ) );
		
		$result = $this->controller->dispatch ( $this->request );
		$response = $this->controller->getResponse ();
		
		$this->assertEquals ( 400, $response->getStatusCode () );
	}
	
	public function testMoveWithWrongAction() {
		$this->kanbanizeService->expects ( $this->any () )->method ( 'moveTask' )->will ( $this->returnCallback ( array (
				$this,
				'getCorrectResult'
		) ) );
		$this->kanbanizeService->expects ( $this->any () )->method ( 'isAcceptable' )->will ( $this->returnCallback ( array (
				$this,
				'isReallyAcceptable'
		) ) );
	
		$this->routeMatch->setParam ( 'id', $this->getTaskId () );
		$this->request->setMethod ( Request::METHOD_PUT );
		$this->request->getHeaders ()->addHeaderLine ( 'Content-Type', 'application/json' );
		$this->request->setContent ( json_encode ( array (
				'boardid' => 3,
				'action' => 'ok'
		) ) );
	
		$result = $this->controller->dispatch ( $this->request );
		$response = $this->controller->getResponse ();
	
		$this->assertEquals ( 406, $response->getStatusCode () );
	}
	
	public function testMoveWithWrongBoardId() {
		$this->kanbanizeService->expects ( $this->any () )->method ( 'moveTask' )->will ( $this->returnCallback ( array (
				$this,
				'getCorrectResult'
		) ) );
		$this->kanbanizeService->expects ( $this->any () )->method ( 'isAcceptable' )->will ( $this->returnCallback ( array (
				$this,
				'isReallyAcceptable'
		) ) );
	
		$this->routeMatch->setParam ( 'id', $this->getTaskId () );
		$this->request->setMethod ( Request::METHOD_PUT );
		$this->request->getHeaders ()->addHeaderLine ( 'Content-Type', 'application/json' );
		$this->request->setContent ( json_encode ( array (
				'boardid' => 'numero3',
				'action' => 'accept'
		) ) );
	
		$result = $this->controller->dispatch ( $this->request );
		$response = $this->controller->getResponse ();
	
		$this->assertEquals ( 406, $response->getStatusCode () );
	}
	
	public function testMoveWithNoParameters() {
		$this->routeMatch->setParam ( 'id', $this->getTaskId () );
		$this->request->setMethod ( Request::METHOD_PUT );
		$this->request->getHeaders ()->addHeaderLine ( 'Content-Type', 'application/json' );
		$result = $this->controller->dispatch ( $this->request );
		$response = $this->controller->getResponse ();
		
		$this->assertEquals ( 400, $response->getStatusCode () );
	}
	
	public function testMoveWithNoId() {
		$this->request->setMethod ( Request::METHOD_PUT );
		$this->request->getHeaders ()->addHeaderLine ( 'Content-Type', 'application/json' );
		$result = $this->controller->dispatch ( $this->request );
		$response = $this->controller->getResponse ();
		$this->assertEquals ( 405, $response->getStatusCode () );
	}
	
	public function testPostNotAllowed() {
		$this->routeMatch->setParam ( 'id', $this->getTaskId () );
		$this->request->setMethod ( Request::METHOD_POST );
		$this->request->getHeaders ()->addHeaderLine ( 'Content-Type', 'application/json' );
		$result = $this->controller->dispatch ( $this->request );
		$response = $this->controller->getResponse ();
		
		$this->assertEquals ( 405, $response->getStatusCode () );
	}
	
	public function testGetNotAllowed() {
		$this->routeMatch->setParam ( 'id', $this->getTaskId () );
		$this->request->setMethod ( Request::METHOD_GET );
		$this->request->getHeaders ()->addHeaderLine ( 'Content-Type', 'application/json' );
		$result = $this->controller->dispatch ( $this->request );
		$response = $this->controller->getResponse ();
		
		$this->assertEquals ( 405, $response->getStatusCode () );
	}
	
	//Back2OnGoingTests
	public function testMoveBackSuccessfullyDone() {
		$this->kanbanizeService->expects ( $this->any () )->method ( 'moveTask' )->will ( $this->returnCallback ( array (
				$this,
				'getCorrectResult'
		) ) );
		$this->kanbanizeService->expects ( $this->any () )->method ( 'canBeMovedBackToOngoing' )->will ( $this->returnCallback ( array (
				$this,
				'canBeMovedBack'
		) ) );
	
		$this->routeMatch->setParam ( 'id', $this->getTaskId () );
		$this->request->setMethod ( Request::METHOD_PUT );
		$this->request->getHeaders ()->addHeaderLine ( 'Content-Type', 'application/json' );
		$this->request->setContent ( json_encode ( array (
				'boardid' => 3,
				'action' => 'ongoing'
		) ) );
	
		$result = $this->controller->dispatch ( $this->request );
		$response = $this->controller->getResponse ();
	
		$this->assertEquals ( 200, $response->getStatusCode () );
	}
	
	public function testMoveBackTaskCanNotBeMovedBack() {
		$this->kanbanizeService->expects ( $this->any () )->method ( 'moveTask' )->will ( $this->returnCallback ( array (
				$this,
				'getCorrectResult'
		) ) );
		$this->kanbanizeService->expects ( $this->any () )->method ( 'canBeMovedBackToOngoing' )->will ( $this->returnCallback ( array (
				$this,
				'canNotBeMovedBack'
		) ) );
	
		$this->routeMatch->setParam ( 'id', $this->getTaskId () );
		$this->request->setMethod ( Request::METHOD_PUT );
		$this->request->getHeaders ()->addHeaderLine ( 'Content-Type', 'application/json' );
		$this->request->setContent ( json_encode ( array (
				'boardid' => 3,
				'action' => 'ongoing'
		) ) );
	
		$result = $this->controller->dispatch ( $this->request );
		$response = $this->controller->getResponse ();
	
		$this->assertEquals ( 400, $response->getStatusCode () );
	}
	
	public function testMoveBackTaskWithWrongAction() {
		$this->kanbanizeService->expects ( $this->any () )->method ( 'moveTask' )->will ( $this->returnCallback ( array (
				$this,
				'getCorrectResult'
		) ) );
		$this->kanbanizeService->expects ( $this->any () )->method ( 'canBeMovedBackToOngoing' )->will ( $this->returnCallback ( array (
				$this,
				'canBeMovedBack'
		) ) );
	
		$this->routeMatch->setParam ( 'id', $this->getTaskId () );
		$this->request->setMethod ( Request::METHOD_PUT );
		$this->request->getHeaders ()->addHeaderLine ( 'Content-Type', 'application/json' );
		$this->request->setContent ( json_encode ( array (
				'boardid' => 3,
				'action' => 'ok'
		) ) );
	
		$result = $this->controller->dispatch ( $this->request );
		$response = $this->controller->getResponse ();
	
		$this->assertEquals ( 406, $response->getStatusCode () );
	}
	
	public function testMoveBackTaskWithWrongBoardId() {
		$this->kanbanizeService->expects ( $this->any () )->method ( 'moveTask' )->will ( $this->returnCallback ( array (
				$this,
				'getCorrectResult'
		) ) );
		$this->kanbanizeService->expects ( $this->any () )->method ( 'canBeMovedBackToOngoing' )->will ( $this->returnCallback ( array (
				$this,
				'canBeMovedBack'
		) ) );
	
		$this->routeMatch->setParam ( 'id', $this->getTaskId () );
		$this->request->setMethod ( Request::METHOD_PUT );
		$this->request->getHeaders ()->addHeaderLine ( 'Content-Type', 'application/json' );
		$this->request->setContent ( json_encode ( array (
				'boardid' => 'numero3',
				'action' => 'ongoing'
		) ) );
	
		$result = $this->controller->dispatch ( $this->request );
		$response = $this->controller->getResponse ();
	
		$this->assertEquals ( 406, $response->getStatusCode () );
	}
	
	public function tearDown() {
	}
	
	private function getTaskId() {
		return 1;
	}
	
	public function getMockedKanbanizeService() {
		return $this->kanbanizeService;
	}
	
	public function getCorrectResult() {
		return 1;
	}
	
	public function isReallyAcceptable() {
		return true;
	}
	
	public function isNotAcceptable() {
		return false;
	}
	
	public function canBeMovedBack(){
		return true;
	}
	
	public function canNotBeMovedBack(){
		return false;
	}
	
}
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


class KanbanizeControllerTest extends \PHPUnit_Framework_TestCase
{
	protected $serviceManager;

	/**
	 * @var \Ora\Kanbanize\KanbanizeService
	 */
	protected $kanbanizeService;
	
    protected $controller;
    protected $request;
    protected $response;
    protected $routeMatch;
    protected $event;
    
    protected function setUp()
    {  		
        $bootstrap = Application::init(include('tests/unit/test.config.php'));
        //$this->controller = new KanbanizeController();
        $this->request = new Request();
        $this->routeMatch = new RouteMatch(array('controller' => 'kanbanize'));
        $this->event = $bootstrap->getMvcEvent();
        
        $this->event->setRouteMatch($this->routeMatch);
        //$this->controller->setEvent($this->event);
        //$this->controller->setEventManager($bootstrap->getEventManager());
        
        $this->serviceManager = $bootstrap->getServiceManager();
        //$this->serviceManager->setFactory('Kanbanize\Service\Kanbanize', new KanbanizeServiceFactory());
        //$this->serviceManager->setFactory('Application\Service\EventStore', new \Application\Service\EventStoreFactory());
        //$this->serviceManager->setFactory('doctrine.entitymanager.orm_default', new EntityManagerFactory());
        
        //$this->kanbanizeService = $this->serviceManager->get('Kanbanize\Service\Kanbanize');
        
        //$boardId = 3;
        
        //$taskId = $this->kanbanizeService->createNewTask(1, "Test Task", $boardId);
        
        //$this->kanbanizeTask = new KanbanizeTask(uniqid(), $boardId, $taskId, new \DateTime(), "Pippo");
        
        $this->controller = $this->getMock('Kanbanize\Controller\KanbanizeController', array('getKanbanizeService'));

        $this->setApplicationConfig( array(
        		'controllers' => array(
        				'invokables' => array(
        						'Kanbanize\Controller\Kanbanize' => $this->controller
        				),
        		),
        ));
        
        $this->kanbanizeService = $this->getMockForAbstractClass(
        		'\Ora\Kanbanize\KanbanizeService',
        		array('moveTask', 'isAcceptable')
        );

        $this->controller->expects($this->any())
        ->method('getKanbanizeService')
        ->will($this->returnCallback(array($this, 'getMockedKanbanizeService')));

        $this->kanbanizeService->expects($this->any())
        ->method('moveTask')
        ->will($this->returnCallback(array($this, 'getCorrectResult')));
        $this->kanbanizeService->expects($this->any())
        ->method('isAcceptable')
        ->will($this->returnCallback(array($this, 'isReallyAcceptable')));
        
        
    }
    
    public function testMoveWithCorrectParameters()
    {
    	$this->request->setUri('http://192.168.56.111/kanbanize/task/'.$this->getTaskId());
    	//$this->routeMatch->setParam('id', 12);
    	$this->request->setMethod(Request::METHOD_PUT);
		$this->request->getHeaders()->addHeaderLine('Content-Type', 'application/json');
    	$this->request->setContent(json_encode(array('boardid' => 3, 'action' => 'accept')));
    	$client = new Client();
    	$client->setAdapter('Zend\Http\Client\Adapter\Curl');
    	$this->response = $client->dispatch($this->request);
    	$this->assertEquals($this->response->getStatusCode(), 200);
	}
	
	public function tearDown() {
		//$this->kanbanizeService->deleteTask($this->kanbanizeTask);
	}
    
	private function getTaskId() {
		return 1;
	}
	
	private function getMockedKanbanizeService() {
		return $this->kanbanizeService;
	}
	
	private function getCorrectResult() {
		return 1;
	}
	
	private function isReallyAcceptable() {
		print_r("I'm acceptable!!!!!1!!11111!!ONE!!!");
		return true;
	}
	
	private function isNotAcceptable() {
		return false;
	}
	
	private function justAnotherMockedMethod() {
		
	}
	
}
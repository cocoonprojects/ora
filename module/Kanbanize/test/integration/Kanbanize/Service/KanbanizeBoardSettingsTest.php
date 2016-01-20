<?php 

namespace Kanbanize;

use IntegrationTest\Bootstrap;
use PHPUnit_Framework_TestCase;
use Prooph\EventStore\EventStore;
use Kanbanize\Controller\BoardsController;
use Zend\Http\Request;
use Zend\Http\Response;
use Zend\Uri\Http;
use Zend\Mvc\MvcEvent;
use Zend\Mvc\Router\Http\TreeRouteStack as HttpRouter;
use Zend\Mvc\Router\RouteMatch;
use ZFX\Test\Authentication\AdapterMock;

class KanbanizeBoardSettingsTest extends \PHPUnit_Framework_TestCase{
	
	protected $controller;
	protected $request;
	protected $response;
	protected $routeMatch;
	protected $event;
	
	public function setup(){
		$serviceManager = Bootstrap::getServiceManager();
	
		$userService = $serviceManager->get('Application\UserService');
		$this->user = $userService->findUser('60000000-0000-0000-0000-000000000000');
	
		$orgService = $serviceManager->get('People\OrganizationService');
		$client = $this->configureKanbanizeClientMock($serviceManager);
		$kanbanizeService = $serviceManager->get('Kanbanize\KanbanizeService');
		$streamService = $serviceManager->get('TaskManagement\StreamService');
		
		$this->controller = new BoardsController($orgService, $streamService, $client, $kanbanizeService);
		$this->request	= new Request();
		$this->routeMatch = new RouteMatch(array('controller' => 'boards'));
		$this->event = new MvcEvent();
		$config = $serviceManager->get('Config');
		$this->event->setRouteMatch($this->routeMatch);
		$this->controller->setEvent($this->event);
		$this->controller->setServiceLocator($serviceManager);
	
		$adapter = new AdapterMock();
		$adapter->setEmail($this->user->getEmail());
		$authService = $serviceManager->get('Zend\Authentication\AuthenticationService');
		$authService->authenticate($adapter);
	
		$pluginManager = $serviceManager->get('ControllerPluginManager');
		$this->controller->setPluginManager($pluginManager);
	}
	
	public function testKanbanizeBoardSettingsSuccess() {
		
		$this->routeMatch->setParam('orgId', '00000000-0000-0000-1000-000000000000');
		$this->routeMatch->setParam('id', '1');
		$this->request->setMethod('post');
		$params = $this->request->getPost();
		$columnMapping = [
				'Requested'=> 0,
				'Approved'=> 10,
				'WIP'=> 20,
				'Testing'=> 20,
				'Production_Release' => 30,
				'Accepted' => 40,
				'Closed' => 50,
				'Archive' => 50
		];
		$params->set('projectId', 1);
		$params->set('streamName', 'foo stream');
		$params->set('mapping', $columnMapping);
		$result = $this->controller->dispatch($this->request);
		$response = $this->controller->getResponse();
		
		$this->assertEquals(201, $response->getStatusCode());
		$arrayResult = json_decode ( $result->serialize (), true );
		$this->assertEquals( '1', $arrayResult['boardId'] );
		$this->assertEquals( 'foo stream', $arrayResult['streamName'] );
		$this->assertEquals( '0', $arrayResult['boardSettings']['columnMapping']['Requested'] );
		$this->assertEquals( '10', $arrayResult['boardSettings']['columnMapping']['Approved'] );
		$this->assertEquals( '20', $arrayResult['boardSettings']['columnMapping']['WIP'] );
		$this->assertEquals( '20', $arrayResult['boardSettings']['columnMapping']['Testing'] );
		$this->assertEquals( '30', $arrayResult['boardSettings']['columnMapping']['Production_Release'] );
		$this->assertEquals( '40', $arrayResult['boardSettings']['columnMapping']['Accepted'] );
		$this->assertEquals( '50', $arrayResult['boardSettings']['columnMapping']['Closed'] );
		$this->assertEquals( '50', $arrayResult['boardSettings']['columnMapping']['Archive'] );
	}
	
	private function configureKanbanizeClientMock($serviceManager){
		$clientMock = $serviceManager->get('Kanbanize\KanbanizeAPI');
		$clientMock->expects(\PHPUnit_Framework_TestCase::once())
		->method('getProjectsAndBoards')
		->willReturn(
				[
					[
						'name' => 'foo project',
						'boards' => [
								[
										"name" => "board 1",
										"id" => 1
								]
						]
					]
				]
		);
		$clientMock->expects(\PHPUnit_Framework_TestCase::once())
		->method('getBoardStructure')
		->willReturn(
				[
					"columns" => [
						[
							"position" => "0",
							"lcname" => "Requested",
							"description" => "",
							"tasksperrow" => "1"
						],
						[
							"position" => "1",
							"lcname" => "Approved",
							"description" => "",
							"tasksperrow" => "1"
						],
						[
							"position" => "2",
							"lcname" => "WIP",
							"description" => "",
							"tasksperrow" => "1"
						],
						[
							"position" => "3",
							"lcname" => "Testing",
							"description" => "",
							"tasksperrow" => "1"
						],
						[
							"position" => "4",
							"lcname" => "Production Release",
							"description" => "",
							"tasksperrow" => "1"
						],
						[
							"position" => "5",
							"lcname" => "Accepted",
							"description" => "",
							"tasksperrow" => "1"
						],
						[
							"position" => "6",
							"lcname" => "Closed",
							"description" => "",
							"tasksperrow" => "1"
						],
						[
							"position" => "7",
							"lcname" => "Archive",
							"description" => "",
							"tasksperrow" => "0"
						]
					]
				]
		);
		return $clientMock;
	}
}
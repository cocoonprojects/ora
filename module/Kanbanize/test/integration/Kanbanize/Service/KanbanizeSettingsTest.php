<?php

namespace Kanbanize;

use IntegrationTest\Bootstrap;
use PHPUnit_Framework_TestCase;
use Prooph\EventStore\EventStore;
use Kanbanize\Controller\SettingsController;
use Zend\Http\Request;
use Zend\Http\Response;
use Zend\Uri\Http;
use Zend\Mvc\MvcEvent;
use Zend\Mvc\Router\Http\TreeRouteStack as HttpRouter;
use Zend\Mvc\Router\RouteMatch;
use ZFX\Test\Authentication\AdapterMock;
use Kanbanize\Service\KanbanizeService;


class KanbanizeSettingsTest extends \PHPUnit_Framework_TestCase{

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
		$kanbanizeService = $serviceManager->get('Kanbanize\KanbanizeService');
		$client = $this->configureKanbanizeClientMock($serviceManager);
		$this->controller = new SettingsController($orgService, $client, $kanbanizeService);
		$this->request	= new Request();
		$this->routeMatch = new RouteMatch(array('controller' => 'settings'));
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

	public function testKanbanizeSettingsSuccess() {

		$this->markTestSkipped('not mantained');

		$this->routeMatch->setParam('orgId', '00000000-0000-0000-1000-000000000000');
		$this->request->setMethod('put')->setContent("subdomain=foo&apiKey=AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA");
		$result = $this->controller->dispatch($this->request);
		$response = $this->controller->getResponse();

		$this->assertEquals(202, $response->getStatusCode());
		$arrayResult = json_decode ( $result->serialize (), true );
		$this->assertEquals( 'foo', $arrayResult['subdomain'] );
		$this->assertEquals( 'foo project', $arrayResult['projects'][0]['name'] );
		$this->assertEquals ( 1, $arrayResult['projects'][0]['boards'][0]['id'] );
		$this->assertEquals ( "board 1", $arrayResult['projects'][0]['boards'][0]['name'] );
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
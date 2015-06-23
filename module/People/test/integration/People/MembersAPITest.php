<?php
/**
 * Created by PhpStorm.
 * User: andreabandera
 * Date: 23/06/15
 * Time: 10:45
 */

namespace People;


use IntegrationTest\Bootstrap;
use People\Controller\MembersController;
use People\Service\OrganizationService;
use Zend\Http\Request;
use Zend\Mvc\MvcEvent;
use Zend\Mvc\Router\RouteMatch;
use Zend\Mvc\Router\Http\TreeRouteStack as HttpRouter;
use ZFX\Test\Authentication\OAuth2AdapterMock;


class MembersAPITest extends \PHPUnit_Framework_TestCase
{
	protected $controller;
	protected $request;
	protected $response;
	protected $routeMatch;
	protected $event;

	/**
	 * @var OrganizationService
	 */
	protected $orgService;

	protected function setUp()
	{
		$serviceManager = Bootstrap::getServiceManager();
		$this->orgService = $serviceManager->get('People\OrganizationService');
		$this->controller = new MembersController($this->orgService);
		$this->request	= new Request();
		$this->routeMatch = new RouteMatch(array('controller' => 'members'));
		$this->event	  = new MvcEvent();
		$config = $serviceManager->get('Config');
		$routerConfig = isset($config['router']) ? $config['router'] : array();
		$router = HttpRouter::factory($routerConfig);

		$this->event->setRouter($router);
		$this->event->setRouteMatch($this->routeMatch);
		$this->controller->setEvent($this->event);
		$this->controller->setServiceLocator($serviceManager);

		$adapter = new OAuth2AdapterMock();
		$adapter->setEmail('phil.toledo@ora.local');
		$authService = $serviceManager->get('Zend\Authentication\AuthenticationService');
		$authService->authenticate($adapter);

		$pluginManager = $serviceManager->get('ControllerPluginManager');
		$this->controller->setPluginManager($pluginManager);
	}

	public function testJoinAnOrganization()
	{
		$this->routeMatch->setParam('orgId', '00000000-0000-0000-1000-000000000000');

		$this->request->setMethod('post');

		$result   = $this->controller->dispatch($this->request);
		$response = $this->controller->getResponse();

		$this->assertEquals(201, $response->getStatusCode());
		$organization = $this->orgService->findOrganization('00000000-0000-0000-1000-000000000000');
		$memberships = $this->orgService->findOrganizationMemberships($organization);
		$this->assertEquals('70000000-0000-0000-0000-000000000000', end($memberships)->getMember()->getId());
	}
}
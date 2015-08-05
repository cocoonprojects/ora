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
use Zend\Authentication\AuthenticationService;
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
	/**
	 * @var AuthenticationService
	 */
	protected $authService;

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
		$adapter->setEmail('paul.smith@ora.local');
		$this->authService = $serviceManager->get('Zend\Authentication\AuthenticationService');
		$this->authService->authenticate($adapter);

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
		$isMember = false;
		foreach($memberships as $m) {
			if($m->getMember()->getId() == $this->authService->getIdentity()->getId()) {
				$isMember = true;
			}
		}
		$this->assertTrue($isMember);
	}
}
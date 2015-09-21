<?php
namespace People;

use IntegrationTest\Bootstrap;
use People\Service\OrganizationService;
use People\Controller\UserProfileController;
use Application\Service\UserService;
use Accounting\Service\AccountService;
use Zend\Http\Request;
use Zend\Mvc\Router\Http\TreeRouteStack as HttpRouter;
use Zend\Mvc\Router\RouteMatch;
use ZFX\Test\Authentication\AdapterMock;
use ZFX\Test\Authentication\OAuth2AdapterMock;
use Zend\Mvc\MvcEvent;
use Accounting\Entity\AccountTransaction;
use Accounting\Entity\Account;

class UserProfileAPITest extends \PHPUnit_Framework_TestCase
{
	protected $controller;
	protected $request;
	protected $response;
	protected $routeMatch;
	
	protected $orgService;
	protected $userService;
	protected $accountService;
	

	protected function setUp()
	{
		$serviceManager = Bootstrap::getServiceManager();
		$this->orgService = $serviceManager->get('People\OrganizationService');
		$this->userService = $serviceManager->get('Application\UserService');
		$this->accountService = $serviceManager->get('Accounting\CreditsAccountsService');
		
		$this->controller = new UserProfileController($this->orgService, $this->userService, $this->accountService);
		
		$this->request	= new Request();
		$this->routeMatch = new RouteMatch(array('controller' => 'user-profiles'));
		$this->event	  = new MvcEvent();
		
		$config = $serviceManager->get('Config');
		$routerConfig = isset($config['router']) ? $config['router'] : array();
		$router = HttpRouter::factory($routerConfig);
		
		$this->event->setRouter($router);
		$this->event->setRouteMatch($this->routeMatch);
		$this->controller->setEvent($this->event);
		$this->controller->setServiceLocator($serviceManager);
		
		$adapter = new AdapterMock();
		$adapter->setEmail('phil.toledo@ora.local');
		$this->authService = $serviceManager->get('Zend\Authentication\AuthenticationService');
		$this->authService->authenticate($adapter);
		
		$pluginManager = $serviceManager->get('ControllerPluginManager');
		$this->controller->setPluginManager($pluginManager);
	}
	
	public function testGetUserProfilePage()
	{
		$this->routeMatch->setParam('orgId', '00000000-0000-0000-1000-000000000000');
		$this->routeMatch->setParam('id', '80000000-0000-0000-0000-000000000000');
		
		$user = $this->userService->findUser('80000000-0000-0000-0000-000000000000');
		$organization = $this->orgService->getOrganization('00000000-0000-0000-1000-000000000000');
		$account = $this->accountService->findPersonalAccount($user, $organization);
		$actualBalance = $account->getBalance()->getValue();
		
		$this->request->setMethod('get');		
		$result   = $this->controller->dispatch($this->request);
		$response = $this->controller->getResponse();
		
		$this->assertEquals(200, $response->getStatusCode());
		$arrayResult = json_decode ( $result->serialize (), true );
		
		$this->assertEquals ( $user->getId (), $arrayResult ['id'] );
		$this->assertEquals ( $user->getFirstname (), $arrayResult ['firstname'] );
		$this->assertEquals ( $user->getLastname (), $arrayResult ['lastname'] );
		$this->assertEquals ( $user->getPicture (), $arrayResult ['picture'] );
		$this->assertEquals ( $user->getEmail (), $arrayResult ['email'] );
		
		$this->assertNotEmpty ( $arrayResult ['_embedded'] ['organization'] );
		$this->assertArrayHasKey ( 'id', $arrayResult ['_embedded'] ['organization'] );
		$this->assertArrayHasKey ( 'name', $arrayResult ['_embedded'] ['organization'] );
		$this->assertArrayHasKey ( 'role', $arrayResult ['_embedded'] ['organization'] );
		$this->assertEquals ( $organization->getId (), $arrayResult ['_embedded'] ['organization'] ['id'] );
		$this->assertEquals ( $organization->getName (), $arrayResult ['_embedded'] ['organization'] ['name'] );
		$this->assertEquals ( 'member', $arrayResult ['_embedded'] ['organization'] ['role'] );
		
		$this->assertNotEmpty ( $arrayResult ['_embedded'] ['credits'] );
		$this->assertEquals ( $actualBalance, $arrayResult ['_embedded'] ['credits'] ['balance'] );
		$this->assertEquals ( 3600, $arrayResult ['_embedded'] ['credits'] ['total'] );
		$this->assertEquals ( 100, $arrayResult ['_embedded'] ['credits'] ['last3M'] );
		$this->assertEquals ( 1100, $arrayResult ['_embedded'] ['credits'] ['last6M'] );
		$this->assertEquals ( 1600, $arrayResult ['_embedded'] ['credits'] ['lastY'] );
	}
}
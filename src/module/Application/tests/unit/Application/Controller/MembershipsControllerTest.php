<?php
namespace Application\Controller;

use UnitTest\Bootstrap;
use Zend\Mvc\Router\Http\TreeRouteStack as HttpRouter;
use Zend\Http\Request;
use Zend\Mvc\MvcEvent;
use Zend\Mvc\Router\RouteMatch;
use PHPUnit_Framework_TestCase;
use Application\Entity\User;
use Application\Service\OrganizationService;
use Application\Controller\Plugin\EventStoreTransactionPlugin;
use Accounting\OrganizationAccount;
use Application\Entity\OrganizationMembership;
use Application\Entity\Organization;

class MembershipsControllerTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * 
	 * @var OrganizationsController
	 */
	protected $controller;
	/**
	 * 
	 * @var Request
	 */
	protected $request;
	/**
	 * 
	 * @var RouteMatch
	 */
	protected $routeMatch;
	/**
	 * 
	 * @var MvcEvent
	 */
	protected $event;
	
	protected function setUp()
	{
		$orgService = $this->getMockBuilder(OrganizationService::class)
			->getMock();
		
		$serviceManager = Bootstrap::getServiceManager();
		$this->controller = new MembershipsController($orgService);
		$this->request	= new Request();
		$this->routeMatch = new RouteMatch(array('controller' => 'memberships'));
		$this->event	  = new MvcEvent();
		$config = $serviceManager->get('Config');
		$routerConfig = isset($config['router']) ? $config['router'] : array();
		$router = HttpRouter::factory($routerConfig);

		$this->event->setRouter($router);
		$this->event->setRouteMatch($this->routeMatch);
		$this->controller->setEvent($this->event);
		$this->controller->setServiceLocator($serviceManager);

		$transaction = $this->getMockBuilder(EventStoreTransactionPlugin::class)
			->disableOriginalConstructor()
			->setMethods(['begin', 'commit', 'rollback'])
			->getMock();
		$this->controller->getPluginManager()->setService('transaction', $transaction);
	}
	
	public function testGetListAsAnonymous() {
		$this->setupAnonymous();
		$result   = $this->controller->dispatch($this->request);
		$response = $this->controller->getResponse();
		
		$this->assertEquals(401, $response->getStatusCode());		 
	}
	
	public function testGetList() {
		$org1 = new Organization('1');
		$org1->setName('Pippo');

		$org2 = new Organization('2');
		$org2->setName('Pluto');
		
		$user = User::create();
		$this->setupLoggedUser($user);
		
		$membership1 = new OrganizationMembership($user, $org1);
		$membership1->setRole(OrganizationMembership::ROLE_ADMIN);
		$membership1->setCreatedAt(new \DateTime());
		$membership1->setCreatedBy($user);
		
		$membership2 = new OrganizationMembership($user, $org2);
		$membership2->setRole(OrganizationMembership::ROLE_MEMBER);
		$membership2->setCreatedAt(new \DateTime());
		$membership2->setCreatedBy($user);
		 
		$this->controller->getOrganizationService()
			->expects($this->once())
			->method('findUserOrganizationMemberships')
			->with($this->equalTo($user))
			->willReturn([$membership1, $membership2]);
		
		$result   = $this->controller->dispatch($this->request);
		$response = $this->controller->getResponse();
		
		$arrayResult = json_decode($result->serialize(), true);
		
		$this->assertEquals(200, $response->getStatusCode());		 
		$this->assertCount(2, $arrayResult['_embedded']['ora:organization-membership']);
		$this->assertEquals(2, $arrayResult['count']);
		$this->assertEquals(2, $arrayResult['total']);
	}
	
	public function testGetListAsNotMemberOfAnyOrg() {
		$user = User::create();
		$this->setupLoggedUser($user);
		
		$this->controller->getOrganizationService()
			->expects($this->once())
			->method('findUserOrganizationMemberships')
			->with($this->equalTo($user))
			->willReturn(array());
		
		$result   = $this->controller->dispatch($this->request);
		$response = $this->controller->getResponse();
		
		$arrayResult = json_decode($result->serialize(), true);
		
		$this->assertEquals(200, $response->getStatusCode());		 
		$this->assertArrayHasKey('_embedded', $arrayResult);
		$this->assertArrayHasKey('ora:organization-membership', $arrayResult['_embedded']);
		$this->assertCount(0, $arrayResult['_embedded']['ora:organization-membership']);
		$this->assertEquals(0, $arrayResult['count']);
		$this->assertEquals(0, $arrayResult['total']);
	}
	
	protected function setupAnonymous() {
		$identity = $this->getMockBuilder('Zend\Mvc\Controller\Plugin\Identity')
			->disableOriginalConstructor()
			->getMock();
		$identity->method('__invoke')->willReturn(null);
		
		$this->controller->getPluginManager()->setService('identity', $identity);
	}
	
	protected function setupLoggedUser(User $user) {
		$identity = $this->getMockBuilder('Zend\Mvc\Controller\Plugin\Identity')
			->disableOriginalConstructor()
			->getMock();
		$identity->method('__invoke')->willReturn(['user' => $user]);
		
		$this->controller->getPluginManager()->setService('identity', $identity);
	}
}
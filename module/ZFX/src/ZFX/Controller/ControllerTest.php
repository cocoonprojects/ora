<?php
namespace ZFX\Controller;

use UnitTest\Bootstrap;
use Zend\Http\Request;
use Zend\Mvc\MvcEvent;
use Zend\Mvc\Router\RouteMatch;
use Zend\Mvc\Router\Http\TreeRouteStack as HttpRouter;
use Application\Service\OrganizationService;
use Application\Controller\Plugin\EventStoreTransactionPlugin;
use Application\Entity\User;
use Application\Entity\Organization as ReadModelOrganization;
use ZFX\Authentication\AuthenticationServiceMock;
use Zend\Mvc\Controller\Plugin\Identity;
use Zend\Mvc\Controller\AbstractController;
use ZFX\Controller\Plugin\IsAllowed;
use Zend\ServiceManager\ServiceManager;

abstract class ControllerTest extends \PHPUnit_Framework_TestCase
{
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
	/**
	 * 
	 * @var AuthenticationServiceMock
	 */
	private $authenticationService;
	
	protected function setUp()
	{
		$serviceManager = Bootstrap::getServiceManager();
		$this->controller = $this->setupController();
		$this->request	= new Request();
		$this->routeMatch = new RouteMatch($this->setupRouteMatch());
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

		$acl = $serviceManager->get('Application\Service\Acl');
		$isAllowed = new IsAllowed($acl);
		$this->controller->getPluginManager()->setService('isAllowed', $isAllowed);
		
		$this->authenticationService = $serviceManager->get('Zend\Authentication\AuthenticationService');
		$identity = new Identity();
		$identity->setAuthenticationService($this->authenticationService);
		$this->controller->getPluginManager()->setService('identity', $identity);
	}
	/**
	 * @return AbstractController
	 */
	protected abstract function setupController();
	/**
	 * @return array
	 */
	protected abstract function setupRouteMatch();
	
	protected function setupAnonymous() {
		$this->authenticationService->setIdentity(null);
	}
	protected function setupLoggedUser(User $user) {
		$this->authenticationService->setIdentity($user);
	}
}
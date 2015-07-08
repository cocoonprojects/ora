<?php
namespace ZFX\Test\Controller;

use UnitTest\Bootstrap;
use Zend\Http\Request;
use Zend\Mvc\MvcEvent;
use Zend\Mvc\Router\RouteMatch;
use Zend\Mvc\Router\Http\TreeRouteStack as HttpRouter;
use Zend\ServiceManager\ServiceManager;
use Application\Entity\User;
use Application\Service\AclFactory;
use ZFX\Acl\Controller\Plugin\IsAllowed;
use ZFX\EventStore\Controller\Plugin\EventStoreTransactionPlugin;

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

		$aclFactory = new AclFactory();
		$acl = $aclFactory->createService($serviceManager);
		$this->controller->getPluginManager()->setService('isAllowed', new IsAllowed($acl));

		$this->setupMore();
	}

	protected abstract function setupController();
	/**
	 * @return array
	 */
	protected abstract function setupRouteMatch();

	protected function setupMore() {}
	
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
	protected function getLoggedUser() {
		return $this->controller->identity()['user'];
	}
}
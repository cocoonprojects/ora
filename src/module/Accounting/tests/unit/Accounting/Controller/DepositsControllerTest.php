<?php
namespace Accounting\Controller;

use Test\Bootstrap;
use Zend\Mvc\Router\Http\TreeRouteStack as HttpRouter;
use Zend\Http\Request;
use Zend\Mvc\MvcEvent;
use Zend\Mvc\Router\RouteMatch;
use PHPUnit_Framework_TestCase;
use Ora\User\User;
use Rhumsaa\Uuid\Uuid;
use Accounting\Account;

class DepositsControllerTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * 
	 * @var DepositsController
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
    
    protected $account;
    
    protected function setUp()
    {
    	$accountService = $this->getMockBuilder('Accounting\Service\AccountService')
    		->getMock();
    	
        $serviceManager = Bootstrap::getServiceManager();
        $this->controller = new DepositsController($accountService);
        $this->request    = new Request();
        $this->routeMatch = new RouteMatch(array('controller' => 'deposits'));
        $this->event      = new MvcEvent();
        $config = $serviceManager->get('Config');
        $routerConfig = isset($config['router']) ? $config['router'] : array();
        $router = HttpRouter::factory($routerConfig);

        $this->event->setRouter($router);
        $this->event->setRouteMatch($this->routeMatch);
        $this->controller->setEvent($this->event);
        $this->controller->setServiceLocator($serviceManager);

    	$transaction = $this->getMockBuilder('ZendExtension\Mvc\Controller\Plugin\EventStoreTransactionPlugin')
    		->disableOriginalConstructor()
    		->setMethods(['begin', 'commit', 'rollback'])
    		->getMock();
        $this->controller->getPluginManager()->setService('transaction', $transaction);
        
    	$user = User::create();
    	$this->setupLoggedUser($user);
    	
        $this->account = Account::create($user);
    }
    
    public function testInvoke() {
    	$this->controller->getAccountService()
    		->expects($this->once())
    		->method('getAccount')
    		->with($this->account->getId())
    		->willReturn($this->account);
    	
    	$this->controller->getAccountService()
    		->method('deposit')
    		->willReturn($this->account);
    	
    	$this->routeMatch->setParam('id', $this->account->getId());

    	$this->request->setMethod('post');
    	$params = $this->request->getPost();
    	$params->set('amount', 100);
    	$params->set('description', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vivamus iaculis.');
    	 
    	$result   = $this->controller->dispatch($this->request);
    	$response = $this->controller->getResponse();
    	
    	$this->assertEquals(201, $response->getStatusCode());
    	$this->assertNotEmpty($response->getHeaders()->get('Location'));
    }

    public function testInvokeWithFloatAmount() {
    	$this->controller->getAccountService()
    		->expects($this->once())
    		->method('getAccount')
    		->with($this->account->getId())
    		->willReturn($this->account);
    	
    	$this->controller->getAccountService()
    		->method('deposit')
    		->willReturn($this->account);
    	
    	$this->routeMatch->setParam('id', $this->account->getId());

    	$this->request->setMethod('post');
    	$params = $this->request->getPost();
    	$params->set('amount', 100.56);
    	$params->set('description', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vivamus iaculis.');
    	 
    	$result   = $this->controller->dispatch($this->request);
    	$response = $this->controller->getResponse();
    	
    	$this->assertEquals(201, $response->getStatusCode());
    	$this->assertNotEmpty($response->getHeaders()->get('Location'));
    }

    public function testInvokeWith0Amount() {
	   	$this->routeMatch->setParam('id', $this->account->getId());

    	$this->request->setMethod('post');
    	$params = $this->request->getPost();
    	$params->set('amount', 0);
    	$params->set('description', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vivamus iaculis.');
    	 
    	$result   = $this->controller->dispatch($this->request);
    	$response = $this->controller->getResponse();
    	
    	$this->assertEquals(400, $response->getStatusCode());
    }

    public function testInvokeWithNoAmount() {
    	$this->routeMatch->setParam('id', $this->account->getId());

    	$this->request->setMethod('post');
    	$params = $this->request->getPost();
    	$params->set('description', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vivamus iaculis.');
    	 
    	$result   = $this->controller->dispatch($this->request);
    	$response = $this->controller->getResponse();
    	
    	$this->assertEquals(400, $response->getStatusCode());
    }

    public function testInvokeWithNegativeAmount() {
    	$this->routeMatch->setParam('id', $this->account->getId());

    	$this->request->setMethod('post');
    	$params = $this->request->getPost();
    	$params->set('amount', -1000);
    	$params->set('description', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vivamus iaculis.');
    	 
    	$result   = $this->controller->dispatch($this->request);
    	$response = $this->controller->getResponse();
    	
    	$this->assertEquals(400, $response->getStatusCode());
    }

    public function testInvokeAsAnonymous() {
		$this->setupAnonymous();

    	$this->routeMatch->setParam('id', $this->account->getId());
		
    	$this->request->setMethod('post');
    	
    	$params = $this->request->getPost();
    	$params->set('amount', 100);
    	$params->set('description', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vivamus iaculis.');
    	    	
    	$result   = $this->controller->dispatch($this->request);
    	$response = $this->controller->getResponse();
    	
    	$this->assertEquals(401, $response->getStatusCode());    	 
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
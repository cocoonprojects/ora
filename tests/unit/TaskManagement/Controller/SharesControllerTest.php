<?php
namespace TaskManagement\Controller;

use Test\Bootstrap;
use Zend\Mvc\Router\Http\TreeRouteStack as HttpRouter;
use Zend\Http\Request;
use Zend\Http\Response;
use Zend\Mvc\MvcEvent;
use Zend\Mvc\Router\RouteMatch;
use PHPUnit_Framework_TestCase;
use Ora\TaskManagement\Task;
use Ora\StreamManagement\Stream;
use Rhumsaa\Uuid\Uuid;
use Ora\User\User;

class SharesControllerTest extends \PHPUnit_Framework_TestCase {
	
    protected $controller;
    protected $request;
    protected $response;
    protected $routeMatch;
    protected $event;
    
    protected $task;
    protected $member1;
    protected $member2;

    protected function setUp()
    {
        $taskServiceStub = $this->getMockBuilder('Ora\TaskManagement\TaskService')->getMock();
        
        $this->member1 = User::create();
        $this->task = Task::create(new Stream(Uuid::uuid4(), $this->member1), 'test', $this->member1);
        
        $this->member2 = User::create();
        $this->task->addMember($this->member2, $this->member2, Task::ROLE_MEMBER);
        
        $this->task->addEstimation(1500, $this->member1);
        $this->task->addEstimation(3100, $this->member2);
        
        $this->task->complete($this->member1);
        
        $serviceManager = Bootstrap::getServiceManager();
        $this->controller = new SharesController($taskServiceStub);
        $this->request    = new Request();
        $this->routeMatch = new RouteMatch(array('controller' => 'shares'));
        $this->event      = new MvcEvent();
        $config = $serviceManager->get('Config');
        $routerConfig = isset($config['router']) ? $config['router'] : array();
        $router = HttpRouter::factory($routerConfig);

        $this->event->setRouter($router);
        $this->event->setRouteMatch($this->routeMatch);
        $this->controller->setEvent($this->event);
        $this->controller->setServiceLocator($serviceManager);
        
        $identity = $this->getMockBuilder('ZendExtension\Mvc\Controller\Plugin\LoggedIdentity')
			->disableOriginalConstructor()
        	->getMock();
    	$identity->method('__invoke')->willReturn($this->member1);
    	$transaction = $this->getMockBuilder('ZendExtension\Mvc\Controller\Plugin\EventStoreTransactionPlugin')
    		->disableOriginalConstructor()
    		->setMethods(['begin', 'commit', 'rollback'])
    		->getMock();
    	
        $this->controller->getPluginManager()->setService('loggedIdentity', $identity);
        $this->controller->getPluginManager()->setService('transaction', $transaction);
    }
    
    public function testAssignSharesAsAnonymous()
    {
        $identity = $this->getMockBuilder('ZendExtension\Mvc\Controller\Plugin\LoggedIdentity')
			->disableOriginalConstructor()
        	->getMock();
    	$identity->method('__invoke')->willReturn(null);
    	$this->controller->getPluginManager()->setService('loggedIdentity', $identity);
    	
    	$this->task->accept($this->member1);
    	$service = $this->controller->getTaskService();
    	$service->method('getTask')
    			->willReturn($this->task);
    	
    	$this->routeMatch->setParam('id', $this->task->getId()->toString());
    	
    	$this->request->setMethod('post');
    	$params = $this->request->getPost();
    	$params->set($this->member1->getId(), 40);
    	$params->set($this->member2->getId(), 60);
    	 
    	$result   = $this->controller->dispatch($this->request);
    	$response = $this->controller->getResponse();
    
    	$this->assertEquals(401, $response->getStatusCode());
    }
    
    public function testAssignShares()
    {
    	$this->task->accept($this->member1);
    	$service = $this->controller->getTaskService();
    	$service->method('getTask')
    			->willReturn($this->task);
    	
    	$this->routeMatch->setParam('id', $this->task->getId()->toString());
    	
    	$this->request->setMethod('post');
    	$params = $this->request->getPost();
    	$params->set($this->member1->getId(), 40);
    	$params->set($this->member2->getId(), 60);
    	 
    	$result   = $this->controller->dispatch($this->request);
    	$response = $this->controller->getResponse();
    
    	$this->assertEquals(201, $response->getStatusCode());
    }
    
    public function testSkipAssignShares()
    {
    	$this->task->accept($this->member1);
    	$service = $this->controller->getTaskService();
    	$service->method('getTask')
    			->willReturn($this->task);
    	
    	$this->routeMatch->setParam('id', $this->task->getId()->toString());
    	
    	$this->request->setMethod('post');
    	 
    	$result   = $this->controller->dispatch($this->request);
    	$response = $this->controller->getResponse();
    
    	$this->assertEquals(201, $response->getStatusCode());
    }
    
    public function testAssignSharesWithMoreThan100Share()
    {
    	$this->task->accept($this->member1);
    	$service = $this->controller->getTaskService();
    	$service->method('getTask')
    			->willReturn($this->task);
    	
    	$this->routeMatch->setParam('id', $this->task->getId()->toString());
    	
    	$this->request->setMethod('post');
    	$params = $this->request->getPost();
    	$params->set($this->member1->getId(), 101);
    	$params->set($this->member2->getId(), 60);
    	 
    	$result   = $this->controller->dispatch($this->request);
    	$response = $this->controller->getResponse();
    
    	$this->assertEquals(400, $response->getStatusCode());
    }
    
    public function testAssignSharesWithLessThan0Share()
    {
    	$this->task->accept($this->member1);
    	$service = $this->controller->getTaskService();
    	$service->method('getTask')
    			->willReturn($this->task);
    	
    	$this->routeMatch->setParam('id', $this->task->getId()->toString());
    	
    	$this->request->setMethod('post');
    	$params = $this->request->getPost();
    	$params->set($this->member1->getId(), 40);
    	$params->set($this->member2->getId(), -1);
    	 
    	$result   = $this->controller->dispatch($this->request);
    	$response = $this->controller->getResponse();
    
    	$this->assertEquals(400, $response->getStatusCode());
    }
    
    public function testAssignSharesWithMoreThan100TotalShares()
    {
    	$this->task->accept($this->member1);
    	$service = $this->controller->getTaskService();
    	$service->method('getTask')
    			->willReturn($this->task);
    	
    	$this->routeMatch->setParam('id', $this->task->getId()->toString());
    	
    	$this->request->setMethod('post');
    	$params = $this->request->getPost();
    	$params->set($this->member1->getId(), 40);
    	$params->set($this->member2->getId(), 70);
    	 
    	$result   = $this->controller->dispatch($this->request);
    	$response = $this->controller->getResponse();
    
    	$this->assertEquals(400, $response->getStatusCode());
    }
    
    public function testAssignSharesWithUnexistingTask()
    {
    	$service = $this->controller->getTaskService();
    	$service->method('getTask')
    			->willReturn(null);
    	
    	$this->routeMatch->setParam('id', $this->task->getId()->toString());
    	
    	$this->request->setMethod('post');
    	$params = $this->request->getPost();
    	$params->set($this->member1->getId(), 40);
    	$params->set($this->member2->getId(), 60);
    	 
    	$result   = $this->controller->dispatch($this->request);
    	$response = $this->controller->getResponse();
    
    	$this->assertEquals(404, $response->getStatusCode());
    }
    
    public function testAssignSharesWithMissingMember()
    {
    	$this->task->accept($this->member1);
    	$service = $this->controller->getTaskService();
    	$service->method('getTask')
    			->willReturn($this->task);
    	
    	$this->routeMatch->setParam('id', $this->task->getId()->toString());
    	
    	$this->request->setMethod('post');
    	$params = $this->request->getPost();
    	$params->set($this->member1->getId(), 100);
    	 
    	$result   = $this->controller->dispatch($this->request);
    	$response = $this->controller->getResponse();
    
    	$this->assertEquals(400, $response->getStatusCode());
    }
    
    public function testAssignSharesByNonMember()
    {
    	$this->task->accept($this->member1);
    	$identity = $this->getMockBuilder('ZendExtension\Mvc\Controller\Plugin\LoggedIdentity')
			->disableOriginalConstructor()
        	->getMock();
    	$identity->method('__invoke')->willReturn(User::create());
    	$this->controller->getPluginManager()->setService('loggedIdentity', $identity);
    	 
    	$service = $this->controller->getTaskService();
    	$service->method('getTask')
    		->willReturn($this->task);
    	 
    	$this->routeMatch->setParam('id', $this->task->getId()->toString());
    	 
    	$this->request->setMethod('post');
    	$params = $this->request->getPost();
    	$params->set($this->member1->getId(), 40);
    	$params->set($this->member2->getId(), 60);
    	
    	$result   = $this->controller->dispatch($this->request);
    	$response = $this->controller->getResponse();
    	
    	$this->assertEquals(403, $response->getStatusCode());
    }
    
    public function testAssignSharesToACompletedTask()
    {
    	$service = $this->controller->getTaskService();
    	$service->method('getTask')
    	->willReturn($this->task);
    	
    	$this->routeMatch->setParam('id', $this->task->getId()->toString());
    	
    	$this->request->setMethod('post');
    	$params = $this->request->getPost();
    	$params->set($this->member1->getId(), 40);
    	$params->set($this->member2->getId(), 60);
    	 
    	$result   = $this->controller->dispatch($this->request);
    	$response = $this->controller->getResponse();
    	 
    	$this->assertEquals(412, $response->getStatusCode());
    }
    
    public function testAssignSharesToANonMembers()
    {
    	$this->task->accept($this->member1);
    	$service = $this->controller->getTaskService();
    	$service->method('getTask')
    		->willReturn($this->task);
    	 
    	$this->routeMatch->setParam('id', $this->task->getId()->toString());
    	 
    	$this->request->setMethod('post');
    	$params = $this->request->getPost();
    	$params->set(User::create()->getId(), 20);
    	$params->set($this->member1->getId(), 20);
    	$params->set($this->member2->getId(), 60);
    	
    	$result   = $this->controller->dispatch($this->request);
    	$response = $this->controller->getResponse();
    	
    	$this->assertEquals(400, $response->getStatusCode());
    }

    public function testAssignSharesToANonMembersExtraShares()
    {
    	$this->task->accept($this->member1);
    	$service = $this->controller->getTaskService();
    	$service->method('getTask')
    		->willReturn($this->task);
    	 
    	$this->routeMatch->setParam('id', $this->task->getId()->toString());
    	 
    	$this->request->setMethod('post');
    	$params = $this->request->getPost();
    	$params->set(User::create()->getId(), 20);
    	$params->set($this->member1->getId(), 40);
    	$params->set($this->member2->getId(), 60);
    	
    	$result   = $this->controller->dispatch($this->request);
    	$response = $this->controller->getResponse();
    	
    	$this->assertEquals(201, $response->getStatusCode());
    }
}
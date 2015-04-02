<?php
namespace TaskManagement\Controller;

use Test\Bootstrap;
use Zend\Http\Request;
use Zend\Http\Response;
use Zend\Mvc\MvcEvent;
use Zend\Mvc\Router\RouteMatch;
use Zend\Mvc\Router\Http\TreeRouteStack as HttpRouter;
use Zend\EventManager\EventManager;
use PHPUnit_Framework_TestCase;
use Rhumsaa\Uuid\Uuid;
use Ora\TaskManagement\Task;
use Ora\User\User;

class SharesControllerTest extends \PHPUnit_Framework_TestCase {
	
    protected $controller;
    protected $request;
    protected $response;
    protected $routeMatch;
    protected $event;
    
    protected $task;
    protected $owner;
    protected $member;
    protected $organization;

    protected function setUp()
    {
        $this->owner = $this->getMockBuilder('Ora\User\User')
        	->getMock();
        $this->owner->method('getId')
        	->willReturn('60000000-0000-0000-0000-000000000000');
    	
        $this->member = $this->getMockBuilder('Ora\User\User')
        	->getMock();
        $this->member->method('getId')
        	->willReturn('70000000-0000-0000-0000-000000000000');
        
        $taskServiceStub = $this->getMockBuilder('Ora\TaskManagement\TaskService')
        	->getMock();
        
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
        
    	$transaction = $this->getMockBuilder('ZendExtension\Mvc\Controller\Plugin\EventStoreTransactionPlugin')
    		->disableOriginalConstructor()
    		->setMethods(['begin', 'commit', 'rollback'])
    		->getMock();
        $this->controller->getPluginManager()->setService('transaction', $transaction);

        $identity = $this->getMockBuilder('Zend\Mvc\Controller\Plugin\Identity')
			->disableOriginalConstructor()
        	->getMock();
    	$identity->method('__invoke')->willReturn(['user' => $this->owner]);
        $this->controller->getPluginManager()->setService('identity', $identity);
        
        $stream = $this->getMockBuilder('TaskManagement\Stream')
        	->disableOriginalConstructor()
        	->getMock();
        $stream->method('getId')
        	->willReturn(Uuid::fromString('00000000-1000-0000-0000-000000000000'));
        
        $this->task = Task::create($stream, 'Cras placerat libero non tempor', $this->owner);
        $this->task->addMember($this->owner, Task::ROLE_OWNER, 'ccde992b-5aa9-4447-98ae-c8115906dcb7');
        $this->task->addMember($this->member, Task::ROLE_MEMBER, 'cdde992b-5aa9-4447-98ae-c8115906dcb7');
        
        $this->task->addEstimation(1500, $this->owner);
        $this->task->addEstimation(3100, $this->member);
        
        $this->task->complete($this->owner);
        
    }
    
    public function testAssignSharesAsAnonymous()
    {
        $identity = $this->getMockBuilder('Zend\Mvc\Controller\Plugin\Identity')
			->disableOriginalConstructor()
        	->getMock();
    	$identity->method('__invoke')->willReturn(null);
    	$this->controller->getPluginManager()->setService('identity', $identity);
    	
    	$this->task->accept($this->owner);
    	$service = $this->controller->getTaskService();
    	$service->method('getTask')
    			->willReturn($this->task);
    	
    	$this->routeMatch->setParam('id', $this->task->getId()->toString());
    	
    	$this->request->setMethod('post');
    	$params = $this->request->getPost();
    	$params->set($this->owner->getId(), 40);
    	$params->set($this->member->getId(), 60);
    	 
    	$result   = $this->controller->dispatch($this->request);
    	$response = $this->controller->getResponse();
    
    	$this->assertEquals(401, $response->getStatusCode());
    }
    
    public function testAssignShares()
    {
    	$this->task->accept($this->owner);
    	$service = $this->controller->getTaskService();
    	$service->method('getTask')
    			->willReturn($this->task);
    	
    	$this->routeMatch->setParam('id', $this->task->getId()->toString());
    	
    	$this->request->setMethod('post');
    	$params = $this->request->getPost();
    	$params->set($this->owner->getId(), 40);
    	$params->set($this->member->getId(), 60);
    	 
    	$result   = $this->controller->dispatch($this->request);
    	$response = $this->controller->getResponse();
    
    	$this->assertEquals(201, $response->getStatusCode());
    }
    
    public function testSkipAssignShares()
    {
    	$this->task->accept($this->owner);
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
    	$this->task->accept($this->owner);
    	$service = $this->controller->getTaskService();
    	$service->method('getTask')
    			->willReturn($this->task);
    	
    	$this->routeMatch->setParam('id', $this->task->getId()->toString());
    	
    	$this->request->setMethod('post');
    	$params = $this->request->getPost();
    	$params->set($this->owner->getId(), 101);
    	$params->set($this->member->getId(), 60);
    	 
    	$result   = $this->controller->dispatch($this->request);
    	$response = $this->controller->getResponse();
    
    	$this->assertEquals(400, $response->getStatusCode());
    }
    
    public function testAssignSharesWithLessThan0Share()
    {
    	$this->task->accept($this->owner);
    	$service = $this->controller->getTaskService();
    	$service->method('getTask')
    			->willReturn($this->task);
    	
    	$this->routeMatch->setParam('id', $this->task->getId()->toString());
    	
    	$this->request->setMethod('post');
    	$params = $this->request->getPost();
    	$params->set($this->owner->getId(), 40);
    	$params->set($this->member->getId(), -1);
    	 
    	$result   = $this->controller->dispatch($this->request);
    	$response = $this->controller->getResponse();
    
    	$this->assertEquals(400, $response->getStatusCode());
    }
    
    public function testAssignSharesWithMoreThan100TotalShares()
    {
    	$this->task->accept($this->owner);
    	$service = $this->controller->getTaskService();
    	$service->method('getTask')
    			->willReturn($this->task);
    	
    	$this->routeMatch->setParam('id', $this->task->getId()->toString());
    	
    	$this->request->setMethod('post');
    	$params = $this->request->getPost();
    	$params->set($this->owner->getId(), 40);
    	$params->set($this->member->getId(), 70);
    	 
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
    	$params->set($this->owner->getId(), 40);
    	$params->set($this->member->getId(), 60);
    	 
    	$result   = $this->controller->dispatch($this->request);
    	$response = $this->controller->getResponse();
    
    	$this->assertEquals(404, $response->getStatusCode());
    }
    
    public function testAssignSharesWithMissingMember()
    {
    	$this->task->accept($this->owner);
    	$service = $this->controller->getTaskService();
    	$service->method('getTask')
    			->willReturn($this->task);
    	
    	$this->routeMatch->setParam('id', $this->task->getId()->toString());
    	
    	$this->request->setMethod('post');
    	$params = $this->request->getPost();
    	$params->set($this->owner->getId(), 100);
    	 
    	$result   = $this->controller->dispatch($this->request);
    	$response = $this->controller->getResponse();
    
    	$this->assertEquals(400, $response->getStatusCode());
    }
    
    public function testAssignSharesByNonMember()
    {
    	$this->task->accept($this->owner);
    	$identity = $this->getMockBuilder('Zend\Mvc\Controller\Plugin\Identity')
			->disableOriginalConstructor()
        	->getMock();
    	$identity->method('__invoke')->willReturn(['user' => User::create()]);
    	$this->controller->getPluginManager()->setService('identity', $identity);
    	 
    	$service = $this->controller->getTaskService();
    	$service->method('getTask')
    		->willReturn($this->task);
    	 
    	$this->routeMatch->setParam('id', $this->task->getId()->toString());
    	 
    	$this->request->setMethod('post');
    	$params = $this->request->getPost();
    	$params->set($this->owner->getId(), 40);
    	$params->set($this->member->getId(), 60);
    	
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
    	$params->set($this->owner->getId(), 40);
    	$params->set($this->member->getId(), 60);
    	 
    	$result   = $this->controller->dispatch($this->request);
    	$response = $this->controller->getResponse();
    	 
    	$this->assertEquals(412, $response->getStatusCode());
    }
    
    public function testAssignSharesToANonMembers()
    {
    	$this->task->accept($this->owner);
    	$service = $this->controller->getTaskService();
    	$service->method('getTask')
    		->willReturn($this->task);
    	 
    	$this->routeMatch->setParam('id', $this->task->getId()->toString());
    	 
    	$this->request->setMethod('post');
    	$params = $this->request->getPost();
    	$params->set(User::create()->getId(), 20);
    	$params->set($this->owner->getId(), 20);
    	$params->set($this->member->getId(), 60);
    	
    	$result   = $this->controller->dispatch($this->request);
    	$response = $this->controller->getResponse();
    	
    	$this->assertEquals(400, $response->getStatusCode());
    }

    public function testAssignSharesToANonMembersExtraShares()
    {
    	$this->task->accept($this->owner);
    	$service = $this->controller->getTaskService();
    	$service->method('getTask')
    		->willReturn($this->task);
    	 
    	$this->routeMatch->setParam('id', $this->task->getId()->toString());
    	 
    	$this->request->setMethod('post');
    	$params = $this->request->getPost();
    	$params->set(User::create()->getId(), 20);
    	$params->set($this->owner->getId(), 40);
    	$params->set($this->member->getId(), 60);
    	
    	$result   = $this->controller->dispatch($this->request);
    	$response = $this->controller->getResponse();
    	
    	$this->assertEquals(201, $response->getStatusCode());
    }
}
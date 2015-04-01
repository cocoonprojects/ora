<?php
namespace TaskManagement\Controller;

use Test\Bootstrap;
use Zend\Mvc\Router\Http\TreeRouteStack as HttpRouter;
use Zend\Http\Request;
use Zend\Http\Response;
use Zend\Mvc\MvcEvent;
use Zend\Mvc\Router\RouteMatch;
use PHPUnit_Framework_TestCase;
use Rhumsaa\Uuid\Uuid;
use TaskManagement\Stream;
use Ora\User\User;
use Application\Organization;
use Ora\ReadModel\Task as ReadModelTask;
use Ora\ReadModel\Stream as ReadModelStream;

class TasksControllerTest extends \PHPUnit_Framework_TestCase {
	
    protected $controller;
    protected $request;
    protected $response;
    protected $routeMatch;
    protected $event;
    protected $authorizeServiceStub;
	    
    protected function setUp(){
    	
    	$serviceManager = Bootstrap::getServiceManager();
    	
    	$taskServiceStub = $this->getMockBuilder('Ora\TaskManagement\TaskService')
        	->getMock();
    	
    	 $streamServiceStub = $this->getMockBuilder('TaskManagement\Service\StreamService')
        	->getMock();
        
        $this->authorizeServiceStub = $this->getMockBuilder('BjyAuthorize\Service\Authorize')
        	->disableOriginalConstructor()
        	->getMock();
        
        $this->controller = new TasksController($taskServiceStub, $streamServiceStub, $this->authorizeServiceStub);
        $this->request    = new Request();
        $this->routeMatch = new RouteMatch(array('controller' => 'tasks'));
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
        
    }
    
    
    public function testCanCreateTaskInEmptyStream() {
    	
    	$organization = Organization::create('My brand new Orga', $this->getLoggedUser());
        $stream = Stream::create($organization, 'Really useful stream', $this->getLoggedUser());
    	
        $this->controller->getTaskService()
        	->expects($this->once())
        	->method('findStreamTasks')
        	->with($stream->getId()->toString())
        	->willReturn(array());
        
        $this->authorizeServiceStub->method('isAllowed')->willReturn(true);	
        	
        $this->request->setMethod('get');
    	$params = $this->request->getQuery();
    	$params->set('streamID', $stream->getId()->toString());
    	
    	$result   = $this->controller->dispatch($this->request);
    	$response = $this->controller->getResponse();

    	$arrayResult = json_decode($result->serialize(), true);
    	
    	$this->assertEquals(200, $response->getStatusCode());
    	$this->assertArrayHasKey('tasks', $arrayResult);
    	$this->assertArrayHasKey('_links', $arrayResult);
        $this->assertArrayHasKey('ora:create', $arrayResult['_links']);
    }
    
	public function testCannotCreateTaskInEmptyStream() {
    	
    	$organization = Organization::create('My brand new Orga', $this->getLoggedUser());
        $stream = Stream::create($organization, 'Really useful stream', $this->getLoggedUser());
    	
        $this->controller->getTaskService()
        	->expects($this->once())
        	->method('findStreamTasks')
        	->with($stream->getId()->toString())
        	->willReturn(array());
        
        $this->authorizeServiceStub->method('isAllowed')->willReturn(false);	
        	
        $this->request->setMethod('get');
    	$params = $this->request->getQuery();
    	$params->set('streamID', $stream->getId()->toString());
    	
    	$result   = $this->controller->dispatch($this->request);
    	$response = $this->controller->getResponse();

    	$arrayResult = json_decode($result->serialize(), true);
    	
    	$this->assertEquals(200, $response->getStatusCode());
    	$this->assertArrayHasKey('tasks', $arrayResult);
    	$this->assertArrayNotHasKey('_links', $arrayResult);        
    }
    
	public function testCanCreateTaskInPopulatedStream() {
    	
        $stream = new ReadModelStream('00000');
    	$task = new ReadModelTask('00001');
    	$task->setCreatedAt(new \DateTime());
    	$task->setStream($stream);
    	//Task::create($stream, "An important task to notice", $this->getLoggedUser());
        
        $this->controller->getTaskService()
        	->expects($this->once())
        	->method('findStreamTasks')
        	->with($stream->getId())
        	->willReturn(array($task));
        
        $this->authorizeServiceStub->method('isAllowed')->willReturn(true);	
        	
        $this->request->setMethod('get');
    	$params = $this->request->getQuery();
    	$params->set('streamID', $stream->getId());
    	
    	$result   = $this->controller->dispatch($this->request);
    	$response = $this->controller->getResponse();

    	$arrayResult = json_decode($result->serialize(), true);
    	
    	$this->assertEquals(200, $response->getStatusCode());
    	$this->assertArrayHasKey('tasks', $arrayResult);
    	$this->assertArrayHasKey('_links', $arrayResult);
    	$this->assertArrayHasKey('ora:create', $arrayResult['_links']);
		
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
<?php
namespace TaskManagement\Controller;

use UnitTest\Bootstrap;
use Zend\Mvc\Router\Http\TreeRouteStack as HttpRouter;
use Zend\Http\Request;
use Zend\Http\Response;
use Zend\Mvc\MvcEvent;
use Zend\Mvc\Router\RouteMatch;
use PHPUnit_Framework_TestCase;
use Rhumsaa\Uuid\Uuid;
use BjyAuthorize\Service\Authorize;
use Application\Entity\User;
use Application\Organization;
use Application\Controller\Plugin\EventStoreTransactionPlugin;
use TaskManagement\Stream;
use TaskManagement\Entity\Task as ReadModelTask;
use TaskManagement\Entity\Stream as ReadModelStream;
use TaskManagement\Service\TaskService;
use TaskManagement\Service\StreamService;
use TaskManagement\Task;
use Application\Service\UserService;
use Zend\Console\Request as ConsoleRequest;

class TasksControllerTest extends \PHPUnit_Framework_TestCase {
	
    protected $controller;
    protected $request;
    protected $response;
    protected $routeMatch;
    protected $event;
    protected $authorizeServiceStub;
    protected $taskServiceStub;
	    
    protected function setUp(){
    	
    	$serviceManager = Bootstrap::getServiceManager();
    	
    	$this->taskServiceStub = $this->getMockBuilder(TaskService::class)
        	->getMock();
    	
    	 $streamServiceStub = $this->getMockBuilder(StreamService::class)
        	->getMock();
        
        $this->authorizeServiceStub = $this->getMockBuilder(Authorize::class)
        	->disableOriginalConstructor()
        	->getMock();
        
        $this->controller = new TasksController($this->taskServiceStub, $streamServiceStub, $this->authorizeServiceStub);
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
        
    	$transaction = $this->getMockBuilder(EventStoreTransactionPlugin::class)
    		->disableOriginalConstructor()
    		->setMethods(['begin', 'commit', 'rollback'])
    		->getMock();
        $this->controller->getPluginManager()->setService('transaction', $transaction);

        $user = User::create();
        $user->setEmail('fake@email.com');
        $this->setupLoggedUser($user);
        
    }
    
    
    public function testCanCreateTaskInEmptyStream() {
    	
    	$stream = $this->setupStream();
    	
        $this->taskServiceStub
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
    	
    	$stream = $this->setupStream();
    	
       $this->taskServiceStub
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
        
       $this->taskServiceStub
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
    
    public function testCannotApplyTimeboxFromGenericHosts(){
    
    	$_SERVER['HTTP_HOST'] = 'www.mydomain.com';
    	
    	$this->routeMatch->setParam('action', 'applytimeboxforshares');
    	$result = $this->controller->dispatch($this->request);
    
    	$response = $this->controller->getResponse();
    
    	$this->assertEquals(404, $response->getStatusCode());
    }
    
	public function testApplyTimeboxToCloseAnAcceptedTasks(){
		
		$_SERVER['HTTP_HOST'] = 'localhost';
		
		$userStub = $this->getMockBuilder(User::class)
        	->disableOriginalConstructor()
        	->getMock();        	
		 $userStub->expects($this->any())
        	->method('getId')        	
        	->willReturn(User::SYSTEM_USER); 		
		
		$userServiceStub = $this->getMockBuilder(UserService::class)
        	->disableOriginalConstructor()
        	->getMock();		 	
        $userServiceStub->expects($this->once())
        	->method('findUser')        	
        	->willReturn($userStub);  
       	
        $this->controller->setUserService($userServiceStub); 	
        	
		$taskToClose = $this->setupTask();
		$taskToClose->addMember($this->getLoggedUser(), Task::ROLE_OWNER);
		$taskToClose->addEstimation(1, $this->getLoggedUser());
		$taskToClose->complete($this->getLoggedUser());
		$taskToClose->accept($this->getLoggedUser());
		
		$this->taskServiceStub
        	->expects($this->once())
        	->method('getAcceptedTaskIdsToNotify')        	
        	->willReturn(array());
		
		$this->taskServiceStub
        	->expects($this->once())
        	->method('getAcceptedTaskIdsToClose')        	
        	->willReturn(array(array('TASK_ID'=>$taskToClose->getId())));
        	
        $this->taskServiceStub
        	->expects($this->once())
        	->method('getTask')        	
        	->willReturn($taskToClose);	
        	
        //dispatch
        $this->routeMatch->setParam('action', 'applytimeboxforshares');
        $result = $this->controller->dispatch($this->request);
		$response = $this->controller->getResponse();
        
        //controllo che il task abbia lo stato corretto
		$this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals(Task::STATUS_CLOSED, $taskToClose->getStatus());
        
	}

	protected function setupStream(){
		
		$organization = Organization::create('My brand new Orga', $this->getLoggedUser());
        return Stream::create($organization, 'Really useful stream', $this->getLoggedUser());
	}
	
	protected function setupTask(){
		
		$stream = $this->setupStream();
		return Task::create($stream, 'task subject', $this->getLoggedUser());		
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
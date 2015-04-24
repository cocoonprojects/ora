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

class TasksControllerTest extends \PHPUnit_Framework_TestCase {
	
	protected $controller;
	protected $request;
	protected $response;
	protected $routeMatch;
	protected $event;
	protected $authorizeServiceStub;
		
	protected function setUp(){
		
		$serviceManager = Bootstrap::getServiceManager();
		
		$taskServiceStub = $this->getMockBuilder(TaskService::class)
			->getMock();
		
		 $streamServiceStub = $this->getMockBuilder(StreamService::class)
			->getMock();
		
		$this->authorizeServiceStub = $this->getMockBuilder(Authorize::class)
			->disableOriginalConstructor()
			->getMock();
		
		$this->controller = new TasksController($taskServiceStub, $streamServiceStub, $this->authorizeServiceStub);
		$this->request	= new Request();
		$this->routeMatch = new RouteMatch(array('controller' => 'tasks'));
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
		$this->assertArrayHasKey('_embedded', $arrayResult);
		$this->assertArrayHasKey('ora:task', $arrayResult['_embedded']);
		$this->assertArrayHasKey('_links', $arrayResult);
		$this->assertArrayHasKey('ora:create', $arrayResult['_links']);
	}
	
	public function testCannotCreateTaskInRejectedStream() {
		
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
		$this->assertArrayHasKey('_embedded', $arrayResult);
		$this->assertArrayHasKey('ora:task', $arrayResult['_embedded']);
		$this->assertArrayHasKey('_links', $arrayResult);		
		$this->assertArrayNotHasKey('ora:create', $arrayResult['_links']);		
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
		$this->assertArrayHasKey('_embedded', $arrayResult);
		$this->assertArrayHasKey('ora:task', $arrayResult['_embedded']);
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
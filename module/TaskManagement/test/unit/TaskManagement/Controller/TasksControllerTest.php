<?php
namespace TaskManagement\Controller;


use ZFX\Test\Controller\ControllerTest;
use Zend\Permissions\Acl\Acl;
use Application\Entity\User;
use TaskManagement\Entity\Task;
use TaskManagement\Entity\Stream;

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


class TasksControllerTest extends ControllerTest
{
	protected $authorizeServiceStub;

	private $user;

	private $stream;

	public function __construct()
	{
		$this->user = User::create();
		$this->user->setFirstname('John');
		$this->user->setLastname('Doe');
		$this->stream = new Stream('00000');
	}

	protected function setupController()
	{
		$taskServiceStub = $this->getMockBuilder(TaskService::class)->getMock();
		$streamServiceStub = $this->getMockBuilder(StreamService::class)->getMock();
		$this->authorizeServiceStub = $this->getMockBuilder(Acl::class)
			->disableOriginalConstructor()
			->getMock();
		return new TasksController($taskServiceStub, $streamServiceStub, $this->authorizeServiceStub);
	}

	protected function setupRouteMatch()
	{
		return ['controller' => 'tasks'];
	}

	public function testCreateTaskInEmptyStream()
	{
		$this->setupLoggedUser($this->user);

		$this->controller->getTaskService()
			->expects($this->once())
			->method('findStreamTasks')
			->with('1')
			->willReturn(array());
		
		$this->authorizeServiceStub->method('isAllowed')->willReturn(true);	
			
		$this->request->setMethod('get');
		$params = $this->request->getQuery();
		$params->set('streamID', '1');
		
		$result   = $this->controller->dispatch($this->request);
		$response = $this->controller->getResponse();

		$arrayResult = json_decode($result->serialize(), true);
		
		$this->assertEquals(200, $response->getStatusCode());
		$this->assertArrayHasKey('_embedded', $arrayResult);
		$this->assertArrayHasKey('ora:task', $arrayResult['_embedded']);
		$this->assertArrayHasKey('_links', $arrayResult);
		$this->assertArrayHasKey('ora:create', $arrayResult['_links']);
	}
	
	public function testCreateTaskInRejectedStream()
	{
		$this->setupLoggedUser($this->user);

		$this->controller->getTaskService()
			->expects($this->once())
			->method('findStreamTasks')
			->with($this->stream->getId())
			->willReturn(array());
		
		$this->authorizeServiceStub->method('isAllowed')->willReturn(false);	
			
		$this->request->setMethod('get');
		$params = $this->request->getQuery();
		$params->set('streamID', $this->stream->getId());
		
		$result   = $this->controller->dispatch($this->request);
		$response = $this->controller->getResponse();

		$arrayResult = json_decode($result->serialize(), true);
		
		$this->assertEquals(200, $response->getStatusCode());
		$this->assertArrayHasKey('_embedded', $arrayResult);
		$this->assertArrayHasKey('ora:task', $arrayResult['_embedded']);
		$this->assertArrayHasKey('_links', $arrayResult);		
		$this->assertArrayNotHasKey('ora:create', $arrayResult['_links']);		
	}
	
	public function testCreateTaskInPopulatedStream()
	{
		$this->setupLoggedUser($this->user);
		$task = new Task('00001');
		$task->setCreatedAt(new \DateTime());
		$task->setStream($this->stream);

		$this->controller->getTaskService()
			->expects($this->once())
			->method('findStreamTasks')
			->with($this->stream->getId())
			->willReturn(array($task));
		
		$this->authorizeServiceStub->method('isAllowed')->willReturn(true);	
			
		$this->request->setMethod('get');
		$params = $this->request->getQuery();
		$params->set('streamID', $this->stream->getId());
		
		$result   = $this->controller->dispatch($this->request);
		$response = $this->controller->getResponse();
        $user = User::create();
        $user->setEmail('fake@email.com');
        $this->setupLoggedUser($user);
        
    }
    
    
	
	public function testGetListAsAnonymous()
	{
		$this->setupAnonymous();
		$this->request->setMethod('get');

		$result   = $this->controller->dispatch($this->request);
		$response = $this->controller->getResponse();

		$this->assertEquals(401, $response->getStatusCode());
	}

	public function testGetEmptyList()
	{
		$this->setupLoggedUser($this->user);
		$this->controller->getTaskService()
			->expects($this->once())
			->method('findTasks')
			->willReturn(array());

		$result   = $this->controller->dispatch($this->request);
		$response = $this->controller->getResponse();

		$arrayResult = json_decode($result->serialize(), true);
		$this->assertEquals(200, $response->getStatusCode());
		$this->assertCount(0, $arrayResult['_embedded']['ora:task']);
		$this->assertNotEmpty($arrayResult['_links']['self']['href']);
		$this->assertEquals(0, $arrayResult['count']);
		$this->assertEquals(0, $arrayResult['total']);
	}

	public function testGetEmptyListFromAStream()
	{
		$this->setupLoggedUser($this->user);
		$this->controller->getTaskService()
			->expects($this->once())
			->method('findStreamTasks')
			->willReturn(array());

		$params = $this->request->getQuery();
		$params->set('streamID', '1');

		$result   = $this->controller->dispatch($this->request);
		$response = $this->controller->getResponse();

		$arrayResult = json_decode($result->serialize(), true);
		$this->assertEquals(200, $response->getStatusCode());
		$this->assertCount(0, $arrayResult['_embedded']['ora:task']);
		$this->assertNotEmpty($arrayResult['_links']['self']['href']);
		$this->assertEquals(0, $arrayResult['count']);
		$this->assertEquals(0, $arrayResult['total']);
	}

	public function testGetList()
	{
		$this->setupLoggedUser($this->user);

		$task1 = new Task('1');
		$task1->setSubject('Lorem ipsum')
			->setCreatedAt(new \DateTime())
			->setCreatedBy($this->user);
		$task1->setMostRecentEditAt($task1->getCreatedAt())
			->setMostRecentEditBy($task1->getCreatedBy());
		$task1->setStream($this->stream)
			->addMember($this->user, Task::ROLE_OWNER, $this->user, $task1->getCreatedAt());

		$this->controller->getTaskService()
			->expects($this->once())
			->method('findTasks')
			->willReturn([
				$task1
			]);

		$result   = $this->controller->dispatch($this->request);
		$response = $this->controller->getResponse();

		$arrayResult = json_decode($result->serialize(), true);
		$this->assertEquals(200, $response->getStatusCode());
		$this->assertCount(1, $arrayResult['_embedded']['ora:task']);
		$this->assertNotEmpty($arrayResult['_links']['self']['href']);
		$this->assertEquals(1, $arrayResult['count']);
		$this->assertEquals(1, $arrayResult['total']);

		$t = $arrayResult['_embedded']['ora:task'][0];
		$this->assertEquals('1', $t['id']);
		$this->assertEquals('Lorem ipsum', $t['subject']);
		$this->assertEquals('task', $t['type']);
		$this->assertEquals($task1->getStatus(), $t['status']);
		$this->assertEquals('John Doe', $t['createdBy']);
		$this->assertArrayHasKey($this->user->getId(), $t['members']);

		$m = $t['members'][$this->user->getId()];
		$this->assertEquals($this->user->getId(), $m['id']);
		$this->assertEquals(Task::ROLE_OWNER, $m['role']);
		$this->assertEquals('John', $m['firstname']);
		$this->assertEquals('Doe', $m['lastname']);
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
<<<<<<< HEAD:module/TaskManagement/test/unit/TaskManagement/Controller/TasksControllerTest.php
		$response = $this->controller->getResponse();
=======
        
        $response = $this->controller->getResponse();
>>>>>>> completed configuration for calling action from localhost:src/module/TaskManagement/tests/unit/TaskManagement/Controller/TasksControllerTest.php
        
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
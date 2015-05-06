<?php
namespace TaskManagement\Controller;

use ZFX\Test\Controller\ControllerTest;
use Zend\Permissions\Acl\Acl;
use Rhumsaa\Uuid\Uuid;
use Application\Entity\User;
use People\Organization;
use TaskManagement\Stream;
use TaskManagement\Entity\Task as ReadModelTask;
use TaskManagement\Entity\Stream as ReadModelStream;
use TaskManagement\Service\TaskService;
use TaskManagement\Service\StreamService;
use TaskManagement\Task;

class TasksControllerTest extends ControllerTest {
	
    protected $aclStub;
        
    protected function setupController()
    {
    	$taskServiceStub = $this->getMockBuilder(TaskService::class)->getMock();
    	$streamServiceStub = $this->getMockBuilder(StreamService::class)->getMock();
    	$this->aclStub = $this->getMockBuilder(Acl::class)
    	->disableOriginalConstructor()
    	->getMock();
    	return new TasksController($taskServiceStub, $streamServiceStub, $this->aclStub);
    }
    
    protected function setupRouteMatch()
    {
    	return ['controller' => 'tasks'];
    }
    
    protected function setUp(){

        parent::setUp();
        $user = User::create();
        $user->setEmail('fake@email.com');
        $this->setupLoggedUser($user);
        
    }
    
    
    public function testCanCreateTaskInEmptyStream() {
    	
    	$stream = $this->setupStream();
    	
       $this->controller->getTaskService()
        	->expects($this->once())
        	->method('findStreamTasks')
        	->with($stream->getId()->toString())
        	->willReturn(array());
        
        $this->aclStub->method('isAllowed')->willReturn(true);	
        	
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
    
	public function testCannotCreateTaskInEmptyStream() {
    	
    	$stream = $this->setupStream();
    	
       	$this->controller->getTaskService()
        	->expects($this->once())
        	->method('findStreamTasks')
        	->with($stream->getId()->toString())
        	->willReturn(array());
        
        $this->aclStub->method('isAllowed')->willReturn(false);	
        	
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
        
     	$this->controller->getTaskService()
        	->expects($this->once())
        	->method('findStreamTasks')
        	->with($stream->getId())
        	->willReturn(array($task));
        
        $this->aclStub->method('isAllowed')->willReturn(true);	
        	
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
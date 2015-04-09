<?php
namespace Kanbanize\Service;

use Zend\Http\Request;
use Zend\Http\Response;
use Zend\Mvc\MvcEvent;
use Zend\Mvc\Router\RouteMatch;
use Zend\Mvc\Router\Http\TreeRouteStack as HttpRouter;
use Zend\EventManager\EventManager;
use PHPUnit_Framework_TestCase;
use Rhumsaa\Uuid\Uuid;
use IntegrationTest\Bootstrap;
use Application\Entity\User;
use Application\Controller\Plugin\EventStoreTransactionPlugin;
use TaskManagement\Task;
use TaskManagement\Service\TaskService;
use Kanbanize\KanbanizeTask;

class SyncTaskListenerTest extends \PHPUnit_Framework_TestCase {
	
    protected $controller;
    protected $request;
    protected $response;
    protected $routeMatch;
    protected $event;
    
    protected $task;
    protected $member;
    protected $organization;
    /**
     * 
     * @var TaskService
     */
    protected $taskService;
    /**
     * 
     * @var User
     */
    protected $owner;

    protected function setUp()
    {
        $serviceManager = Bootstrap::getServiceManager();
        $this->taskService = $serviceManager->get('TaskManagement\TaskService');
        
        $userService = $serviceManager->get('Application\UserService');
        $this->owner = $userService->findUser('60000000-0000-0000-0000-000000000000');
    }
    
    public function testExecuteACompletedTask() {
    	$task = $this->taskService->getTask('00000000-0000-0000-0000-000000000110');
    	$this->assertInstanceOf(KanbanizeTask::class, $task);
    	$this->assertEquals(KanbanizeTask::STATUS_COMPLETED, $task->getStatus());
    	
    	$task->execute($this->owner);
    }
}
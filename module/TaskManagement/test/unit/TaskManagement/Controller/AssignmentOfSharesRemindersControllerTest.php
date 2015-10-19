<?php
namespace TaskManagement\Controller;

use Application\Entity\User;
use People\Entity\Organization;
use TaskManagement\Entity\Stream;
use TaskManagement\Entity\Task;
use TaskManagement\Service\NotifyMailListener;
use TaskManagement\Service\TaskService;
use Zend\Mvc\Router\RouteMatch;
use ZFX\Test\Controller\ControllerTest;

class AssignmentOfSharesRemindersControllerTest extends ControllerTest
{
	/**
	 * @var User
	 */
	private $systemUser;

	protected $task;
	protected $owner;
	protected $member;
	protected $taskServiceStub;

	public function setupMore()
	{
		$this->systemUser = User::create()->setRole(User::ROLE_SYSTEM);
	}

	protected function setupController()
	{
		$this->task = new Task('1', new Stream('1', new Organization('1')));
		$this->owner = User::create()->setRole(User::ROLE_USER)->setEmail('taskowner@orateam.com');
		$this->member = User::create()->setRole(User::ROLE_USER)->setEmail('taskmember@orateam.com');

		$this->task->addMember($this->owner, Task::ROLE_OWNER, $this->owner, new \DateTime())
			->addMember($this->member, Task::ROLE_MEMBER, $this->member, new \DateTime())
			->setStatus(Task::STATUS_ACCEPTED);

		//Task Service Mock
		$this->taskServiceStub = $this->getMockBuilder(TaskService::class)->getMock();
		$this->taskServiceStub->method('findAcceptedTasksBefore')->willReturn([$this->task]);

		$notifyMailListenerStub = $this->getMockBuilder(NotifyMailListener::class)->disableOriginalConstructor()->getMock();
		return new RemindersController($notifyMailListenerStub, $this->taskServiceStub);
	}

	protected function setupRouteMatch()
	{
		return ['controller' => 'reminders'];
	}

	public function testCreateAsAnonymous()
	{
		$this->setupAnonymous();
		$this->routeMatch->setParam('type', 'assignment-of-shares');
		$this->request->setMethod('post');
		$result = $this->controller->dispatch($this->request);
		$response = $this->controller->getResponse();
		$this->assertEquals(401, $response->getStatusCode());
	}

	public function testCreateAsSystemUser()
	{
		$this->setupLoggedUser($this->systemUser);
		$this->routeMatch->setParam('type', 'assignment-of-shares');
		$this->request->setMethod('post');
		$result = $this->controller->dispatch($this->request);
		$response = $this->controller->getResponse();
		$this->assertEquals(201, $response->getStatusCode());
	}

	public function testCreateANonExistentReminder()
	{
		$this->setupLoggedUser($this->systemUser);
		$this->routeMatch->setParam('type', 'foo');
		$this->request->setMethod('post');
		$result = $this->controller->dispatch($this->request);
		$response = $this->controller->getResponse();
		$this->assertEquals(404, $response->getStatusCode());
	}

	public function testCreateWithoutReminderType()
	{
		$this->setupLoggedUser($this->systemUser);
		$this->request->setMethod('post');
		$result = $this->controller->dispatch($this->request);
		$response = $this->controller->getResponse();
		$this->assertEquals(404, $response->getStatusCode());
	}
}
<?php
namespace TaskManagement\Controller;

use Application\Entity\User;
use People\Entity\Organization;
use People\Service\OrganizationService;
use TaskManagement\Entity\Stream;
use TaskManagement\Entity\Task;
use TaskManagement\Service\NotifyMailListener;
use TaskManagement\Service\TaskService;
use Zend\Mvc\Router\RouteMatch;
use ZFX\Test\Controller\ControllerTest;

class AddEstimationRemindersControllerTest extends ControllerTest
{
	/**
	 * @var User
	 */
	private $systemUser;

	protected $org;
	protected $task;
	protected $owner;
	protected $member;
	protected $taskServiceStub;

	public function setupMore()
	{
		$this->systemUser = $this->getMockBuilder(User::class)
			->disableOriginalConstructor()
			->getMock();
		$this->systemUser->method('getRoleId')->willReturn(User::ROLE_SYSTEM);
	}

	protected function setupController()
	{
		$this->owner = User::create()->setRole(User::ROLE_USER);
		$this->member = User::create()->setRole(User::ROLE_USER);

		$this->org = new Organization('1');
		$this->task = new Task('1', new Stream('1', $this->org));
		$this->task->addMember($this->owner, Task::ROLE_OWNER, $this->owner, new \DateTime())
			->addMember($this->member, Task::ROLE_MEMBER, $this->member, new \DateTime())
			->setStatus(Task::STATUS_ONGOING);

		//Task Service Mock
		$this->taskServiceStub = $this->getMockBuilder(TaskService::class)->getMock();
		$this->orgServiceStub = $this->getMockBuilder(OrganizationService::class)->getMock();
		$this->orgServiceStub
			 ->method('findOrganization')
			 ->willReturn($this->org);

		$notifyMailListenerStub = $this->getMockBuilder(NotifyMailListener::class)->disableOriginalConstructor()->getMock();
		$notifyMailListenerStub
			->method('remindEstimation')
			->willReturn([]);

		return new RemindersController(
			$notifyMailListenerStub,
			$this->taskServiceStub,
			$this->orgServiceStub
		);
	}

	protected function setupRouteMatch()
	{
		return ['controller' => 'reminders', 'orgId' => $this->task->getOrganizationId()];
	}

	public function testSendReminderAsAnonymous()
	{
		$this->setupAnonymous();
		$this->routeMatch->setParam('id', $this->task->getId());
		$this->routeMatch->setParam('type', 'add-estimation');
		$this->request->setMethod('post');

		$result = $this->controller->dispatch($this->request);
		$response = $this->controller->getResponse();

		$this->assertEquals(401, $response->getStatusCode());
	}

	public function testCreateWithoutReminderType()
	{
		$this->setupLoggedUser($this->systemUser);
		$this->routeMatch->setParam('id', $this->task->getId());
		$this->request->setMethod('post');
		$this->taskServiceStub
			->expects($this->once())
			->method('findTask')
			->with($this->task->getId())
			->willReturn($this->task);

		$result = $this->controller->dispatch($this->request);
		$response = $this->controller->getResponse();

		$this->assertEquals(404, $response->getStatusCode());
	}

	public function testCreateWithWrongReminderType()
	{
		$this->setupLoggedUser($this->systemUser);
		$this->routeMatch->setParam('id', $this->task->getId());
		$this->routeMatch->setParam('type', 'foo');
		$this->request->setMethod('post');
		$this->taskServiceStub
			->expects($this->once())
			->method('findTask')
			->with($this->task->getId())
			->willReturn($this->task);

		$result = $this->controller->dispatch($this->request);
		$response = $this->controller->getResponse();

		$this->assertEquals(404, $response->getStatusCode());
	}

	public function testSendReminderNoTask()
	{
		$this->setupLoggedUser($this->owner);
		$this->routeMatch->setParam('type', 'add-estimation');
		$this->request->setMethod('post');

		$result = $this->controller->dispatch($this->request);
		$response = $this->controller->getResponse();

		$this->assertEquals(404, $response->getStatusCode());
	}

	public function testSendReminderNotExistingTask()
	{
		$this->setupLoggedUser($this->owner);
		$this->routeMatch->setParam('id', $this->task->getId());
		$this->routeMatch->setParam('type', 'add-estimation');
		$this->request->setMethod('post');
		$this->taskServiceStub
			->method('findTask')
			->willReturn(null);

		$result = $this->controller->dispatch($this->request);
		$response = $this->controller->getResponse();

		$this->assertEquals(404, $response->getStatusCode());
	}

	public function testSendReminder()
	{
		$this->setupLoggedUser($this->owner);
		$this->routeMatch->setParam('id', $this->task->getId());
		$this->routeMatch->setParam('type', 'add-estimation');
		$this->request->setMethod('post');
		$this->taskServiceStub
			->expects($this->once())
			->method('findTask')
			->with($this->task->getId())
			->willReturn($this->task);

		$result = $this->controller->dispatch($this->request);
		$response = $this->controller->getResponse();

		$this->assertEquals(201, $response->getStatusCode());
	}
}
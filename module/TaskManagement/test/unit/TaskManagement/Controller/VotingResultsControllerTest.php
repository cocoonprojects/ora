<?php
namespace TaskManagement\Controller;

use Application\Entity\User;
//use People\Entity\Organization;
use TaskManagement\Stream;
use TaskManagement\Task;
use TaskManagement\TaskInterface;
use People\Organization;
use TaskManagement\Service\NotifyMailListener;
use TaskManagement\Service\TaskService;
use Zend\Mvc\Router\RouteMatch;
use ZFX\Test\Controller\ControllerTest;

class VotingResultsControllerTest extends ControllerTest
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
		$this->systemUser = $this->getMockBuilder(User::class)
			->disableOriginalConstructor()
			->getMock();
		$this->systemUser->method('getRoleId')->willReturn(User::ROLE_SYSTEM);
	}

	protected function setupController()
	{
		$this->owner = User::create()->setRole(User::ROLE_USER);
		$this->member = User::create()->setRole(User::ROLE_USER);

		$organization = Organization::create('Dummy organization', $this->owner);
		$stream = Stream::create($organization, "Dummy stream", $this->owner);

		$this->owner->addMembership($organization);
		$this->member->addMembership($organization);

		$this->task = Task::create($stream, 'Cras placerat libero non tempor', $this->owner);
		$this->task->addMember($this->owner, Task::ROLE_OWNER);
		$this->task->addMember($this->member, Task::ROLE_MEMBER);
		$this->task->open($this->owner);
		$this->task->execute($this->owner);

		//Task Service Mock
		$this->taskServiceStub = $this->getMockBuilder(TaskService::class)->getMock();
		return new VotingResultsController($this->taskServiceStub);
	}

	protected function setupRouteMatch()
	{
		return ['controller' => 'voting-results', 'orgId' => $this->task->getOrganizationId()];
	}

	public function testTimeboxedCompletedWorkItemApproval()
	{
		$this->taskServiceStub
			->method('countVotesForItem')
			->willReturn(['votesAgainst' => 0, 'votesFor' => 2]);
		$this->taskServiceStub
			->method('findItemsBefore')
			->willReturn([$this->task]);
		$this->taskServiceStub
			->method('getTask')
			->willReturn($this->task);

		$this->task->addEstimation(1500, $this->owner);
		$this->task->addEstimation(3100, $this->member);
		$this->task->complete($this->owner);

		$this->setupLoggedUser($this->systemUser);
		$this->routeMatch->setParam('type', 'completed-items');
		$this->request->setMethod('post');

		$result = $this->controller->dispatch($this->request);
		$response = $this->controller->getResponse();

		$this->assertEquals(200, $response->getStatusCode());
		$this->assertEquals($this->task->getStatus(), TaskInterface::STATUS_ACCEPTED);
	}
}
<?php

namespace Kanbanize\Controller;

use ZFX\Test\Controller\ControllerTest;
use People\Entity\Organization;
use People\Service\OrganizationService;
use Kanbanize\Service\NotificationService;
use Rhumsaa\Uuid\Uuid;
use Application\Entity\User;
use Kanbanize\Service\KanbanizeAPI;
use Kanbanize\Service\KanbanizeService;
use TaskManagement\Service\TaskService;
use Application\Service\UserService;
use TaskManagement\Service\StreamService;
use TaskManagement\Stream;
use Kanbanize\Entity\KanbanizeStream;

class ImportControllerTest extends ControllerTest {

	/**
	 * @var ReadModelOrganization
	 */
	protected $organization;
	/**
	 * @var User
	 */
	protected $user;

	protected function setupController(){
		$orgService = $this->getMockBuilder(OrganizationService::class)->getMock();
		$client = $this->getMockBuilder(KanbanizeAPI::class)->getMock();
		$kanbanizeService = $this->getMockBuilder(KanbanizeService::class)->getMock();
		$taskService = $this->getMockBuilder(TaskService::class)->getMock();
		$userService = $this->getMockBuilder(UserService::class)->getMock();
		$streamService = $this->getMockBuilder(StreamService::class)->getMock();
		return new ImportsController($orgService, $client, $kanbanizeService, $taskService, $userService, $streamService);
	}

	protected function setupMore() {
		$this->user = User::create();
		$this->user->setFirstname('Stephen');
		$this->user->setLastname('Hero');
		$this->user->setRole(User::ROLE_USER);
		$this->organization = new Organization('00000');
	}

	protected function setupRouteMatch(){
		return ['controller' => 'import'];
	}

	public function testImportAsAnonymous()
	{
		$this->markTestSkipped('not mantained');

		$this->setupAnonymous();
		$this->controller->getOrganizationService()
			->expects($this->once())
			->method('findOrganization')
			->with($this->organization->getId())
			->willReturn($this->organization);
		$this->request->setMethod('post');
		$this->routeMatch->setParam('orgId', $this->organization->getId());
		$this->controller->dispatch($this->request);
		$response = $this->controller->getResponse();

		$this->assertEquals(401, $response->getStatusCode());
	}

	public function testImportWithNotAllowedUser()
	{
		$this->setupLoggedUser($this->user);
		$this->controller->getOrganizationService()
			->expects($this->once())
			->method('findOrganization')
			->with($this->organization->getId())
			->willReturn($this->organization);
		$this->request->setMethod('post');
		$this->routeMatch->setParam('orgId', $this->organization->getId());
		$this->controller->dispatch($this->request);
		$response = $this->controller->getResponse();

		$this->assertEquals(403, $response->getStatusCode());
	}

	public function testImportSuccess(){
		$this->markTestSkipped('not mantained');

		$this->user->addMembership($this->organization);
		$this->setupLoggedUser($this->user);

		$this->controller->getOrganizationService()
			->expects($this->once())
			->method('findOrganization')
			->with($this->organization->getId())
			->willReturn($this->organization);

		$wm_organization = \People\Organization::create("new", $this->user);
		$wm_organization->setSettings(\People\Organization::KANBANIZE_SETTINGS, [
				"apiKey" => "aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa",
				"accountSubdomain" => "fooDomain",
				"boards" => [
					"010" => []
				]
		], $this->user);
		$this->controller->getOrganizationService()
			->expects($this->once())
			->method('getOrganization')
			->with($this->organization->getId())
			->willReturn($wm_organization);

		$stream = Stream::create($wm_organization, "fake", $this->user);
		$this->controller->getStreamService()
			->expects($this->once())
			->method('getStream')
			->with("010")
			->willReturn($stream);

		$this->controller->getKanbanizeService()
			->expects($this->once())
			->method('findStreamByBoardId')
			->with("010")
			->willReturn(new KanbanizeStream("010", $this->organization));

		$this->controller->getKanbanizeClient()
			->expects($this->once())
			->method('getAllTasks')
			->willReturn([]);

		$this->controller->getTaskService()
			->expects($this->once())
			->method('findTasks')
			->willReturn([]);

		$this->request->setMethod('post');
		$this->routeMatch->setParam('orgId', $this->organization->getId());
		$result = $this->controller->dispatch($this->request);
		$response = $this->controller->getResponse();
		$arrayResult = json_decode($result->serialize(), true);
		$this->assertEquals ( 0, $arrayResult['createdTasks'] );
		$this->assertEquals ( 0, $arrayResult['updatedTasks'] );
		$this->assertEquals ( 0, $arrayResult['deletedTasks'] );
		$this->assertEmpty($arrayResult['errors']);
		$this->assertEquals(200, $response->getStatusCode());
	}
}
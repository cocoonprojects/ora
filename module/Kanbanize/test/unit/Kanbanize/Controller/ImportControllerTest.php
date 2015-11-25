<?php

namespace Kanbanize\Controller;

use ZFX\Test\Controller\ControllerTest;
use People\Entity\Organization;
use Kanbanize\Service\ImportDirector;
use People\Service\OrganizationService;
use TaskManagement\Service\NotificationService;
use Rhumsaa\Uuid\Uuid;
use Application\Entity\User;

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
		$importDirector = $this->getMockBuilder(ImportDirector::class)->disableOriginalConstructor()->getMock();
		$notificationService = $this->getMockBuilder(NotificationService::class)->disableOriginalConstructor()->getMock();
		return new ImportsController($orgService, $importDirector, $notificationService);
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
		
		$this->user->addMembership($this->organization);
		$this->setupLoggedUser($this->user);

		$this->controller->getOrganizationService()
			->expects($this->once())
			->method('findOrganization')
			->with($this->organization->getId())
			->willReturn($this->organization);
		$wm_organization = \People\Organization::create("new", $this->user);
		$this->controller->getOrganizationService()
			->expects($this->once())
			->method('getOrganization')
			->with($this->organization->getId())
			->willReturn($wm_organization);
		$this->controller->getKanbanizeImporter()->expects($this->once())
			->method('import')
			->willReturn([]);
		$this->controller->getNotificationService()->expects($this->once())
			->method('sendKanbanizeImportResultMail')
			->willReturn([]);

		$this->request->setMethod('post');
		$this->routeMatch->setParam('orgId', $this->organization->getId());
		$result = $this->controller->dispatch($this->request);
		$response = $this->controller->getResponse();
		
		$this->assertEquals(200, $response->getStatusCode());
	}
}
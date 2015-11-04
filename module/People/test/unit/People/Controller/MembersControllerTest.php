<?php
namespace People\Controller;

use Application\Service\UserService;
use People\Organization;
use Rhumsaa\Uuid\Uuid;
use ZFX\Test\Controller\ControllerTest;
use People\Service\OrganizationService;
use Application\Entity\User;
use People\Entity\Organization as ReadModelOrganization;
use People\Entity\OrganizationMembership;

class MembersControllerTest extends ControllerTest
{
	/**
	 * @var ReadModelOrganization
	 */
	protected $organization;
	/**
	 * @var User
	 */
	protected $user;
	/**
	 * @var User
	 */
	protected $user2;

	protected function setupController()
	{
		$this->organization = new ReadModelOrganization(Uuid::uuid4()->toString());
		$orgService = $this->getMockBuilder(OrganizationService::class)->getMock();
		$orgService->expects($this->once())
			->method('findOrganization')
			->with($this->organization->getId())
			->willReturn($this->organization);

		$this->user = User::create();
		$this->user->setFirstname('John');
		$this->user->setLastname('Doe');
		$this->user->setRole(User::ROLE_USER);

		$this->user2 = User::create();
		$this->user2->setFirstname('Jane');
		$this->user2->setLastname('Doe');
		$this->user2->setEmail('jane.doe@foo.com');
		$this->user2->setRole(User::ROLE_USER);

		return new MembersController(
			$orgService,
			$this->getMockBuilder(UserService::class)->getMock()
		);
	}
	
	protected function setupRouteMatch()
	{
		return ['orgId' => $this->organization->getId()];
	}
	
	public function testGetListAsAnonymous()
	{
		$this->setupAnonymous();

		$this->controller->dispatch($this->request);
		$response = $this->controller->getResponse();
		
		$this->assertEquals(401, $response->getStatusCode());
	}

	public function testGetListWithNotAllowedUser()
	{
		$this->setupLoggedUser($this->user);
		
		$this->controller->dispatch($this->request);
		$response = $this->controller->getResponse();
		
		$this->assertEquals(403, $response->getStatusCode());
	}

	public function testGetEmptyList()
	{
		$this->controller->getOrganizationService()
			->expects($this->once())
			->method('findOrganizationMemberships')
			->with($this->equalTo($this->organization))
			->willReturn([]);

		$this->user->addMembership($this->organization);
		$this->setupLoggedUser($this->user);

		$result   = $this->controller->dispatch($this->request);
		$response = $this->controller->getResponse();

		$this->assertEquals(200, $response->getStatusCode());
		
		$arrayResult = json_decode($result->serialize(), true);
		$this->assertCount(0, $arrayResult['_embedded']['ora:member']);
		$this->assertEquals(0, $arrayResult['count']);
		$this->assertEquals(0, $arrayResult['total']);
		$this->assertArrayNotHasKey('next', $arrayResult['_links']);
		$this->assertArrayHasKey('first', $arrayResult['_links']);
		$this->assertArrayHasKey('last', $arrayResult['_links']);
	}
	
	public function testGetCompleteList()
	{
		$this->user->addMembership($this->organization);
		$this->setupLoggedUser($this->user);

		$memberships[] = $this->user->getMembership($this->organization);

		$this->user2->addMembership($this->organization, OrganizationMembership::ROLE_ADMIN);
		$memberships[] = $this->user2->getMembership($this->organization);
		
		$this->controller->getOrganizationService()
			->expects($this->once())
			->method('findOrganizationMemberships')
			->with($this->organization)
			->willReturn($memberships);
		
		$this->controller->getOrganizationService()
			->expects($this->once())
			->method('countOrganizationMemberships')
			->with($this->organization)
			->willReturn(sizeof($memberships));
		
		$result   = $this->controller->dispatch($this->request);
		$response = $this->controller->getResponse();
		
		$this->assertEquals(200, $response->getStatusCode());
		
		$arrayResult = json_decode($result->serialize(), true);
		$this->assertEquals(2, $arrayResult['count']);
		$this->assertEquals(2, $arrayResult['total']);
		$this->assertArrayNotHasKey('next', $arrayResult['_links']);
		$this->assertNotEmpty($arrayResult['_links']['first']['href']);
		$this->assertNotEmpty($arrayResult['_links']['last']['href']);
		$this->assertArrayHasKey($this->user->getId(), $arrayResult['_embedded']['ora:member']);
		$this->assertArrayHasKey($this->user2->getId(), $arrayResult['_embedded']['ora:member']);
		$this->assertEquals($this->user->getFirstname(), $arrayResult['_embedded']['ora:member'][$this->user->getId()]['firstname']);
		$this->assertEquals($this->user->getLastname(), $arrayResult['_embedded']['ora:member'][$this->user->getId()]['lastname']);
	}

	public function testGetIncompleteList()
	{
		$this->user->addMembership($this->organization);
		$this->setupLoggedUser($this->user);

		$memberships[] = $this->user->getMembership($this->organization);

		$this->controller->getOrganizationService()
			->expects($this->once())
			->method('findOrganizationMemberships')
			->with($this->organization)
			->willReturn($memberships);

		$this->controller->getOrganizationService()
			->expects($this->once())
			->method('countOrganizationMemberships')
			->with($this->organization)
			->willReturn(5);

		$params = $this->request->getQuery();
		$params->set('limit', 1);

		$result   = $this->controller->dispatch($this->request);
		$response = $this->controller->getResponse();

		$this->assertEquals(200, $response->getStatusCode());

		$arrayResult = json_decode($result->serialize(), true);
		$this->assertEquals(1, $arrayResult['count']);
		$this->assertEquals(5, $arrayResult['total']);
		$this->assertNotEmpty($arrayResult['_links']['next']['href']);
		$this->assertNotEmpty($arrayResult['_links']['first']['href']);
		$this->assertNotEmpty($arrayResult['_links']['last']['href']);
		$this->assertArrayHasKey($this->user->getId(), $arrayResult['_embedded']['ora:member']);
		$this->assertEquals($this->user->getFirstname(), $arrayResult['_embedded']['ora:member'][$this->user->getId()]['firstname']);
		$this->assertEquals($this->user->getLastname(), $arrayResult['_embedded']['ora:member'][$this->user->getId()]['lastname']);
	}

	public function testCreateAsAnonymous()
	{
		$this->setupAnonymous();

		$this->request->setMethod('post');

		$this->controller->dispatch($this->request);
		$response = $this->controller->getResponse();

		$this->assertEquals(401, $response->getStatusCode());
	}

	public function testCreateAnAlreadyMember()
	{
		$organization = Organization::create('Lorem ipsum', $this->user);
		$this->controller->getOrganizationService()
			->expects($this->once())
			->method('getOrganization')
			->with($this->organization->getId())
			->willReturn($organization);

		$this->setupLoggedUser($this->user);

		$this->request->setMethod('post');

		$this->controller->dispatch($this->request);
		$response = $this->controller->getResponse();

		$this->assertEquals(204, $response->getStatusCode());
	}

	public function testCreate()
	{
		$organization = Organization::create('Lorem ipsum', User::create());

		$this->controller->getOrganizationService()
			->expects($this->once())
			->method('getOrganization')
			->with($this->organization->getId())
			->willReturn($organization);

		$this->setupLoggedUser($this->user);

		$this->request->setMethod('post');

		$this->controller->dispatch($this->request);
		$response = $this->controller->getResponse();

		$this->assertEquals(201, $response->getStatusCode());
		$this->arrayHasKey($this->user->getId(), $organization->getMembers());
		$this->assertEquals(Organization::ROLE_MEMBER, $organization->getMembers()[$this->user->getId()]['role']);
	}

	public function testDeleteAsAnonymous()
	{
		$this->setupAnonymous();

		$this->request->setMethod('delete');

		$this->controller->dispatch($this->request);
		$response = $this->controller->getResponse();

		$this->assertEquals(401, $response->getStatusCode());
	}

	public function testDeleteNotAMember()
	{
		$organization = Organization::create('Lorem ipsum', User::create());
		$this->controller->getOrganizationService()
			->expects($this->once())
			->method('getOrganization')
			->with($this->organization->getId())
			->willReturn($organization);

		$this->setupLoggedUser($this->user);

		$this->request->setMethod('delete');

		$this->controller->dispatch($this->request);
		$response = $this->controller->getResponse();

		$this->assertEquals(204, $response->getStatusCode());
	}

	public function testDeleteOrganizationMember()
	{
		$organization = Organization::create('Lorem ipsum', $this->user);
		$this->controller->getOrganizationService()
			->expects($this->once())
			->method('getOrganization')
			->with($this->organization->getId())
			->willReturn($organization);

		$this->setupLoggedUser($this->user);

		$this->request->setMethod('delete');

		$result   = $this->controller->dispatch($this->request);
		$response = $this->controller->getResponse();

		$this->assertEquals(200, $response->getStatusCode());
		$this->assertArrayNotHasKey($this->user->getId(), $organization->getMembers());
	}

	public function testGetAsAnonymous()
	{
		$this->setupAnonymous();

		$this->routeMatch->setParam('id', $this->user2->getId());
		$this->controller->dispatch($this->request);
		$response = $this->controller->getResponse();

		$this->assertEquals(401, $response->getStatusCode());
	}

	public function testGetANotExistingMember()
	{
		$this->controller->getUserService()
			->expects($this->once())
			->method('findUser')
			->with($this->user2->getId())
			->willReturn(null);
		$this->setupLoggedUser($this->user);

		$this->routeMatch->setParam('id', $this->user2->getId());
		$this->controller->dispatch($this->request);
		$response = $this->controller->getResponse();

		$this->assertEquals(404, $response->getStatusCode());
	}

	public function testGetAsNotAuthorizedUser()
	{
		$this->user2->addMembership($this->organization);
		$this->controller->getUserService()
			->expects($this->once())
			->method('findUser')
			->with($this->user2->getId())
			->willReturn($this->user2);
		$this->setupLoggedUser($this->user);

		$this->routeMatch->setParam('id', $this->user2->getId());
		$this->controller->dispatch($this->request);
		$response = $this->controller->getResponse();

		$this->assertEquals(403, $response->getStatusCode());
	}

	public function testGet()
	{
		$this->user2->addMembership($this->organization);
		$this->controller->getUserService()
			->expects($this->once())
			->method('findUser')
			->with($this->user2->getId())
			->willReturn($this->user2);
		$this->user->addMembership($this->organization);
		$this->setupLoggedUser($this->user);

		$this->routeMatch->setParam('id', $this->user2->getId());
		$result = $this->controller->dispatch($this->request);
		$response = $this->controller->getResponse();

		$this->assertEquals(200, $response->getStatusCode());
		$arrayResult = json_decode ( $result->serialize (), true );
		$this->assertEquals($this->user2->getId(), $arrayResult['id']);
		$this->assertEquals($this->user2->getFirstname(), $arrayResult['firstname']);
		$this->assertEquals($this->user2->getLastname(), $arrayResult['lastname']);
		$this->assertEquals($this->user2->getEmail(), $arrayResult['email']);
		$this->assertEquals($this->user2->getPicture(), $arrayResult['picture']);

		$membership = $this->user2->getMembership($this->organization);
		$this->assertEquals($membership->getRole(), $arrayResult['role']);
	}

}
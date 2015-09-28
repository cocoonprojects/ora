<?php
namespace Accounting\Controller;

use Accounting\Entity\OrganizationAccount;
use Accounting\Entity\PersonalAccount;
use Accounting\Service\AccountService;
use Application\Entity\User;
use Application\Service\UserService;
use People\Entity\Organization;
use People\Service\OrganizationService;
use ZFX\Test\Controller\ControllerTest;

/**
 * Class AccountsControllerTest
 * @package Accounting\Controller
 * @group accounting
 */
class AccountsControllerTest extends ControllerTest
{
	/**
	 * @var Organization
	 */
	protected $organization;
	/**
	 * @var User
	 */
	protected $user;
	
	protected function setupController()
	{
		$this->user = User::create();
		$this->user->setEmail('john.doe@foo.com');

		$userService = $this->getMockBuilder(UserService::class)->getMock();
		$userService
			->expects($this->any())
			->method('findUserByEmail')
			->with($this->user->getEmail())
			->willReturn($this->user);

		$this->organization = new Organization('1');
		$orgServiceStub = $this->getMockBuilder(OrganizationService::class)->getMock();
		$orgServiceStub
			->method('findOrganization')
			->willReturn($this->organization);

		$account = new PersonalAccount('1', $this->organization);
		$account->addHolder($this->user);

		$accountService = $this->getMockBuilder(AccountService::class)->getMock();
		$accountService
			->expects($this->any())
			->method('findPersonalAccount')
			->with($this->user, $this->organization)
			->willReturn($account);

		return new AccountsController($accountService, $userService, $this->acl, $orgServiceStub);
	}
	
	protected function setupRouteMatch()
	{
		return [
			'controller' => 'accounts',
			'orgId'      => $this->organization->getId()
		];
	}

	public function testGetListAsAnonymous()
	{
		$this->setupAnonymous();

		$result   = $this->controller->dispatch($this->request);
		$response = $this->controller->getResponse();

		$this->assertEquals(401, $response->getStatusCode());
	}

	public function testGetListAsOrganizationNotMember()
	{
		$this->setupLoggedUser($this->user);

		$result   = $this->controller->dispatch($this->request);
		$response = $this->controller->getResponse();

		$this->assertEquals(403, $response->getStatusCode());
	}

	public function testGetListWithoutCriteria()
	{
		$this->user->addMembership($this->organization);
		$this->setupLoggedUser($this->user);

		$result   = $this->controller->dispatch($this->request);
		$response = $this->controller->getResponse();

		$this->assertEquals(400, $response->getStatusCode());
	}

	public function testGetListByEmail()
	{
		$this->user->addMembership($this->organization);
		$this->setupLoggedUser($this->user);

		$params = $this->request->getQuery();
		$params->set('email', $this->user->getEmail());

		$result   = $this->controller->dispatch($this->request);
		$response = $this->controller->getResponse();

		$this->assertEquals(200, $response->getStatusCode());
		$arrayResult = json_decode($result->serialize(), true);
		$this->assertCount(1, $arrayResult['_embedded']['ora:account']);
		$this->assertNotEmpty($arrayResult['_links']['self']['href']);
		$this->assertEquals(1, $arrayResult['count']);
		$this->assertEquals(1, $arrayResult['total']);
		$this->assertArrayNotHasKey('next', $arrayResult['_links']);
		$this->assertArrayNotHasKey('ora:deposit', $arrayResult['_embedded']['ora:account'][0]['_links']);
		$this->assertArrayNotHasKey('ora:withdrawal', $arrayResult['_embedded']['ora:account'][0]['_links']);
		$this->assertArrayNotHasKey('ora:incoming-transfer', $arrayResult['_embedded']['ora:account'][0]['_links']);
		$this->assertArrayNotHasKey('ora:outgoing-transfer', $arrayResult['_embedded']['ora:account'][0]['_links']);
	}
}
<?php
namespace Accounting\Controller;

use Accounting\Entity\OrganizationAccount;
use Accounting\Service\AccountService;
use Application\Entity\User;
use People\Entity\Organization;
use People\Service\OrganizationService;
use ZFX\Test\Controller\ControllerTest;

/**
 * Class OrganizationAccountControllerTest
 * @package Accounting\Controller
 * @group accounting
 */
class OrganizationStatementControllerTest extends ControllerTest
{
	/**
	 * @var Organization
	 */
	protected $organization;
	/**
	 * @var OrganizationAccount
	 */
	protected $account;
	/**
	 * @var User
	 */
	protected $user;
	
	protected function setupController()
	{
		$this->user = User::create();

		$this->organization = new Organization('1');
		$this->organization->setName('Lorem ipsum dolor sit amet');

		$this->account = new OrganizationAccount('2', $this->organization);

		$accountService = $this->getMockBuilder(AccountService::class)->getMock();
		$accountService
			->method('findOrganizationAccount')
			->with($this->organization)
			->willReturn($this->account);

		$orgServiceStub = $this->getMockBuilder(OrganizationService::class)->getMock();
		$orgServiceStub
			->method('findOrganization')
			->willReturn($this->organization);
		return new OrganizationStatementController($accountService, $this->acl, $orgServiceStub);
	}
	
	protected function setupRouteMatch()
	{
		return [
			'controller' => 'organization-statement',
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

	public function testGetListAsNotHolder()
	{
		$this->user->addMembership($this->organization);
		$this->setupLoggedUser($this->user);

		$result   = $this->controller->dispatch($this->request);
		$response = $this->controller->getResponse();

		$this->assertEquals(200, $response->getStatusCode());
		$arrayResult = json_decode($result->serialize(), true);
		$this->assertNotEmpty($arrayResult['organization']);
		$this->assertArrayHasKey('transactions', $arrayResult);
		$this->assertNotEmpty($arrayResult['_links']['self']['href']);
		$this->assertArrayNotHasKey('ora:deposit', $arrayResult['_links']);
		$this->assertArrayNotHasKey('ora:withdrawal', $arrayResult['_links']);
		$this->assertArrayNotHasKey('ora:incoming-transfer', $arrayResult['_links']);
		$this->assertArrayNotHasKey('ora:outgoing-transfer', $arrayResult['_links']);
	}

	public function testGetList()
	{
		$this->user->addMembership($this->organization);
		$this->account->addHolder($this->user);
		$this->setupLoggedUser($this->user);

		$result   = $this->controller->dispatch($this->request);
		$response = $this->controller->getResponse();

		$this->assertEquals(200, $response->getStatusCode());
		$arrayResult = json_decode($result->serialize(), true);
		$this->assertNotEmpty($arrayResult['organization']);
		$this->assertArrayHasKey('transactions', $arrayResult);
		$this->assertNotEmpty($arrayResult['_links']['self']['href']);
		$this->assertNotEmpty($arrayResult['_links']['ora:deposit']['href']);
		$this->assertNotEmpty($arrayResult['_links']['ora:withdrawal']['href']);
		$this->assertNotEmpty($arrayResult['_links']['ora:incoming-transfer']['href']);
		$this->assertNotEmpty($arrayResult['_links']['ora:outgoing-transfer']['href']);
	}
}
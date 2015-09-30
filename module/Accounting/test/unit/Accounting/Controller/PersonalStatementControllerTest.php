<?php
namespace Accounting\Controller;

use Accounting\Entity\PersonalAccount;
use Accounting\Service\AccountService;
use Application\Entity\User;
use People\Entity\Organization;
use People\Service\OrganizationService;
use ZFX\Test\Controller\ControllerTest;
use Accounting\Entity\Deposit;
use Accounting\Entity\Accounting\Entity;

/**
 * Class PersonalStatementControllerTest
 * @package Accounting\Controller
 * @group accounting
 */
class PersonalStatementControllerTest extends ControllerTest
{
	/**
	 * @var Organization
	 */
	protected $organization;
	/**
	 * @var PersonalAccount
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

		$this->account = new PersonalAccount('2', $this->organization);

		$accountService = $this->getMockBuilder(AccountService::class)->getMock();

		$orgServiceStub = $this->getMockBuilder(OrganizationService::class)->getMock();
		$orgServiceStub
			->method('findOrganization')
			->willReturn($this->organization);
		return new PersonalStatementController($accountService, $this->acl, $orgServiceStub);
	}
	
	protected function setupRouteMatch()
	{
		return [
			'controller' => 'personal-statement',
			'orgId'     => $this->organization->getId()
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

		$this->controller->getAccountService()
			->expects($this->once())
			->method('findPersonalAccount')
			->with($this->user, $this->organization)
			->willReturn($this->account);

		$result   = $this->controller->dispatch($this->request);
		$response = $this->controller->getResponse();

		$this->assertEquals(403, $response->getStatusCode());
	}

	public function testGetListAsNotHolder()
	{
		$this->user->addMembership($this->organization);
		$this->setupLoggedUser($this->user);

		$this->controller->getAccountService()
			->expects($this->once())
			->method('findPersonalAccount')
			->with($this->user, $this->organization)
			->willReturn($this->account);

		$result   = $this->controller->dispatch($this->request);
		$response = $this->controller->getResponse();

		$this->assertEquals(403, $response->getStatusCode());
	}

	public function testGetList()
	{
		$this->account->addHolder($this->user);
		$this->setupLoggedUser($this->user);

		$this->controller->getAccountService()
			->expects($this->once())
			->method('findPersonalAccount')
			->with($this->user, $this->organization)
			->willReturn($this->account);

		$deposit = new Deposit($this->account);
		$deposit->setCreatedBy($this->user);

		$this->controller->getAccountService()
			->expects($this->once())
			->method('findTransactions')
			->with($this->account)
			->willReturn([$deposit]);

		$this->controller->getAccountService()
			->expects($this->once())
			->method('countTransactions')
			->with($this->account)
			->willReturn(1);

		$result   = $this->controller->dispatch($this->request);
		$response = $this->controller->getResponse();

		$this->assertEquals(200, $response->getStatusCode());
		$arrayResult = json_decode($result->serialize(), true);
		$this->assertNotEmpty($arrayResult['organization']);
		$this->assertArrayHasKey('transactions', $arrayResult);
		$this->assertArrayHasKey('count', $arrayResult);
		$this->assertArrayHasKey('total', $arrayResult);
		$this->assertEquals($arrayResult['count'], $arrayResult['total']);
		$this->assertArrayNotHasKey('next', $arrayResult['_links']);
		$this->assertNotEmpty($arrayResult['_links']['self']['href']);
		$this->assertArrayNotHasKey('ora:deposit', $arrayResult['_links']);
		$this->assertArrayNotHasKey('ora:withdrawal', $arrayResult['_links']);
		$this->assertArrayNotHasKey('ora:incoming-transfer', $arrayResult['_links']);
		$this->assertArrayNotHasKey('ora:outgoing-transfer', $arrayResult['_links']);
	}
	
	public function testGetListWithPagination(){
		
		$this->account->addHolder($this->user);
		$this->setupLoggedUser($this->user);
		
		$params = $this->request->getQuery();
		$params->set('limit', 1);
		
		$this->controller->getAccountService()
			->expects($this->once())
			->method('findPersonalAccount')
			->with($this->user, $this->organization)
			->willReturn($this->account);

		$this->controller->getAccountService()
			->expects($this->once())
			->method('countTransactions')
			->with($this->account)
			->willReturn(2);

		$deposit = new Deposit($this->account);
		$deposit->setCreatedBy($this->user);

		$this->controller->getAccountService()
			->expects($this->once())
			->method('findTransactions')
			->with($this->account)
			->willReturn([$deposit]);

		$result   = $this->controller->dispatch($this->request);
		$response = $this->controller->getResponse();

		$this->assertEquals(200, $response->getStatusCode());
		$arrayResult = json_decode($result->serialize(), true);
		$this->assertNotEmpty($arrayResult['organization']);
		$this->assertArrayHasKey('transactions', $arrayResult);
		$this->assertArrayHasKey('count', $arrayResult);
		$this->assertArrayHasKey('total', $arrayResult);
		$this->assertLessThan($arrayResult['total'], $arrayResult['count']);
		$this->assertNotEmpty($arrayResult['_links']['next']['href']);
		$this->assertNotEmpty($arrayResult['_links']['self']['href']);
		$this->assertArrayNotHasKey('ora:deposit', $arrayResult['_links']);
		$this->assertArrayNotHasKey('ora:withdrawal', $arrayResult['_links']);
		$this->assertArrayNotHasKey('ora:incoming-transfer', $arrayResult['_links']);
		$this->assertArrayNotHasKey('ora:outgoing-transfer', $arrayResult['_links']);
	}
}
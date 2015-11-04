<?php
namespace Accounting\Controller;

use Accounting\Entity\Balance;
use Accounting\Entity\Deposit;
use Accounting\Entity\PersonalAccount;
use Accounting\Entity\Transfer;
use Accounting\Entity\Withdrawal;
use Accounting\Service\AccountService;
use Application\Entity\User;
use Application\Service\UserService;
use People\Entity\Organization;
use People\Service\OrganizationService;
use Rhumsaa\Uuid\Uuid;
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
	protected $user1;
	/**
	 * @var User
	 */
	protected $user2;
	/**
	 * @var PersonalAccount
	 */
	protected $account1;
	/**
	 * @var PersonalAccount
	 */
	protected $account2;

	protected function setupController()
	{
		$this->organization = new Organization(Uuid::uuid4()->toString());
		$orgServiceStub = $this->getMockBuilder(OrganizationService::class)->getMock();
		$orgServiceStub
			->expects($this->once())
			->method('findOrganization')
			->with($this->organization->getId())
			->willReturn($this->organization);

		$this->user1 = User::create();

		$this->user2 = User::create();
		$this->user2->setEmail('john.doe@foo.com');

		$this->account1 = new PersonalAccount(Uuid::uuid4()->toString(), $this->organization);
		$this->account1->addHolder($this->user1);

		$this->account2 = new PersonalAccount(Uuid::uuid4()->toString(), $this->organization);
		$this->account2->addHolder($this->user2);

		return new AccountsController($orgServiceStub,
			$this->getMockBuilder(AccountService::class)->getMock(),
			$this->getMockBuilder(UserService::class)->getMock());
	}
	
	protected function setupRouteMatch()
	{
		return [
			'orgId' => $this->organization->getId()
		];
	}

	public function testGetListAsAnonymous()
	{
		$this->setupAnonymous();

		$this->controller->dispatch($this->request);
		$response = $this->controller->getResponse();

		$this->assertEquals(401, $response->getStatusCode());
	}

	public function testGetListAsOrganizationNotMember()
	{
		$this->setupLoggedUser($this->user1);

		$this->controller->dispatch($this->request);
		$response = $this->controller->getResponse();

		$this->assertEquals(403, $response->getStatusCode());
	}

	public function testGetListWithoutCriteria()
	{
		$this->user1->addMembership($this->organization);
		$this->setupLoggedUser($this->user1);

		$this->controller->dispatch($this->request);
		$response = $this->controller->getResponse();

		$this->assertEquals(400, $response->getStatusCode());
	}

	public function testGetListByEmail()
	{
		$this->controller->getUserService()
			->expects($this->once())
			->method('findUserByEmail')
			->with($this->user2->getEmail())
			->willReturn($this->user2);

		$this->controller->getAccountService()
			->expects($this->once())
			->method('findPersonalAccount')
			->with($this->user2, $this->organization)
			->willReturn($this->account2);

		$this->user1->addMembership($this->organization);
		$this->setupLoggedUser($this->user1);

		$params = $this->request->getQuery();
		$params->set('email', $this->user2->getEmail());

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

	public function testGetAsAnonymous()
	{
		$this->setupAnonymous();

		$this->routeMatch->setParam ( 'id', $this->account2->getId () );

		$this->controller->dispatch($this->request);
		$response = $this->controller->getResponse();

		$this->assertEquals(401, $response->getStatusCode());
	}

	public function testGetAsNotAuthorizedUser()
	{
		$this->controller->getAccountService()
			->expects($this->once())
			->method('findAccount')
			->with($this->account2->getId())
			->willReturn($this->account2);

		$this->setupLoggedUser($this->user1);

		$this->routeMatch->setParam ( 'id', $this->account2->getId () );

		$this->controller->dispatch($this->request);
		$response = $this->controller->getResponse();

		$this->assertEquals(403, $response->getStatusCode());
	}

	public function testGetNotExistingAccount()
	{
		$this->controller->getAccountService()
			->expects($this->once())
			->method('findAccount')
			->with($this->account2->getId())
			->willReturn(null);

		$this->user1->addMembership($this->organization);
		$this->setupLoggedUser($this->user1);

		$this->routeMatch->setParam ( 'id', $this->account2->getId () );

		$this->controller->dispatch($this->request);
		$response = $this->controller->getResponse();

		$this->assertEquals(404, $response->getStatusCode());
	}

	public function testGetEmptyAccount()
	{
		$this->account2->setBalance(
			new Balance(0, new \DateTime)
		);

		$this->controller->getAccountService()
			->expects($this->once())
			->method('findAccount')
			->with($this->account2->getId())
			->willReturn($this->account2);

		$this->controller->getAccountService()
			->expects($this->once())
			->method('findTransactions')
			->with($this->account2, null, null)
			->willReturn([]);

		$this->user1->addMembership($this->organization);
		$this->setupLoggedUser($this->user1);

		$this->routeMatch->setParam ( 'id', $this->account2->getId () );

		$result = $this->controller->dispatch($this->request);
		$response = $this->controller->getResponse();

		$this->assertEquals(200, $response->getStatusCode());
		$arrayResult = json_decode ( $result->serialize (), true );

		$this->assertEquals ( 0, $arrayResult['balance'] );
		$this->assertEquals ( 0, $arrayResult['total'] );
		$this->assertEquals ( 0, $arrayResult['last3M'] );
		$this->assertEquals ( 0, $arrayResult['last6M'] );
		$this->assertEquals ( 0, $arrayResult['last1Y'] );
	}

	public function testGetAccount()
	{
		$this->account2->setBalance(
			new Balance(1300, new \DateTime)
		);

		$this->controller->getAccountService()
			->expects($this->once())
			->method('findAccount')
			->with($this->account2->getId())
			->willReturn($this->account2);

		$transactions[] = new Transfer($this->account1, $this->account2, 1000);
		$transactions[] = new Transfer($this->account2, $this->account1, -500);
		$transactions[] = new Transfer($this->account1, $this->account2, 700);
		$transactions[] = new Deposit($this->account2, 300);
		$transactions[] = new Withdrawal($this->account2, -200);
		$this->controller->getAccountService()
			->expects($this->once())
			->method('findTransactions')
			->with($this->account2, null, null)
			->willReturn($transactions);

		$this->user1->addMembership($this->organization);
		$this->setupLoggedUser($this->user1);

		$this->routeMatch->setParam ( 'id', $this->account2->getId () );

		$result = $this->controller->dispatch($this->request);
		$response = $this->controller->getResponse();

		$this->assertEquals(200, $response->getStatusCode());
		$arrayResult = json_decode ( $result->serialize (), true );

		$this->assertEquals (1300, $arrayResult['balance'] );
		$this->assertEquals (2000, $arrayResult['total'] );
		$this->assertEquals (2000, $arrayResult['last3M'] );
		$this->assertEquals (2000, $arrayResult['last6M'] );
		$this->assertEquals (2000, $arrayResult['last1Y'] );
	}
}
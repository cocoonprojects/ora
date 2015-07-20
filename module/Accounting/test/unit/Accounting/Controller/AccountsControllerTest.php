<?php
namespace Accounting\Controller;

use Accounting\Entity\Account;
use Accounting\Entity\OrganizationAccount;
use Accounting\Service\AccountService;
use Application\Entity\User;
use Application\Controller\Plugin\EventStoreTransactionPlugin;
use People\Entity\Organization;
use Zend\Permissions\Acl\Acl;
use ZFX\Test\Controller\ControllerTest;

class AccountsControllerTest extends ControllerTest
{
	protected $account;
	
	protected function setupController()
	{
		$accountService = $this->getMockBuilder(AccountService::class)->getMock();
		return new AccountsController($accountService, $this->acl);
	}
	
	protected function setupRouteMatch()
	{
		return ['controller' => 'accounts'];
	}

	public function testGetListAsAnonymous()
	{
		$this->setupAnonymous();

		$result   = $this->controller->dispatch($this->request);
		$response = $this->controller->getResponse();

		$this->assertEquals(401, $response->getStatusCode());
	}

	public function testGetList()
	{
		$user = User::create();
		$user->setRole(User::ROLE_USER);
		$this->setupLoggedUser($user);

		$organization = new Organization('1');

		$account = new Account('1', $organization);
		$account->addHolder($user);

		$organizationAccount = new OrganizationAccount('2', $organization);
		$organizationAccount->addHolder($user);

		$this->controller->getAccountService()
			->expects($this->once())
			->method('findAccounts')
			->with($user)
			->willReturn([
				$account,
				$organizationAccount
			]);

		$result   = $this->controller->dispatch($this->request);
		$response = $this->controller->getResponse();

		$this->assertEquals(200, $response->getStatusCode());
		$arrayResult = json_decode($result->serialize(), true);
		$this->assertCount(2, $arrayResult['_embedded']['ora:account']);
		$this->assertNotEmpty($arrayResult['_links']['self']['href']);
		$this->assertEquals(2, $arrayResult['count']);
		$this->assertEquals(2, $arrayResult['total']);
		$this->assertNotEmpty($arrayResult['_embedded']['ora:account'][1]['_links']['ora:deposit']['href']);
		$this->assertNotEmpty($arrayResult['_embedded']['ora:account'][0]['_links']['ora:withdraw']['href']);
		$this->assertArrayNotHasKey('ora:deposit', $arrayResult['_embedded']['ora:account'][0]['_links']);
		$this->assertArrayNotHasKey('ora:withdraw', $arrayResult['_embedded']['ora:account'][1]['_links']);
	}
}
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

class StatementsControllerTest extends ControllerTest
{
	protected $account;
	
	protected function setupController()
	{
		$accountService = $this->getMockBuilder(AccountService::class)->getMock();
		$acl = $this->getMockBuilder(Acl::class)
			->disableOriginalConstructor()
			->getMock();
		$acl->method('isAllowed')->willReturn(true);
		return new StatementsController($accountService, $acl);
	}
	
	protected function setupRouteMatch()
	{
		return ['controller' => 'statements'];
	}

	public function testGetListAsAnonymous()
	{
		$this->setupAnonymous();

		$this->routeMatch->setParam('id', '1');

		$result   = $this->controller->dispatch($this->request);
		$response = $this->controller->getResponse();

		$this->assertEquals(401, $response->getStatusCode());
	}
}
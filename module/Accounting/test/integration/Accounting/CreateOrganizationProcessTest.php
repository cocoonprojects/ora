<?php

namespace Accounting;

use Zend\EventManager\ListenerAggregateInterface;
use IntegrationTest\Bootstrap;
use Accounting\Service\AccountService;
use Accounting\Service\CreatePersonalAccountListener;
use Accounting\Service\AccountCommandsListener;
use Application\Service\OrganizationService;
use Application\Entity\User;

class CreateOrganizationProcessTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * 
	 * @var OrganizationService
	 */
	private $organizationService;
	/**
	 * 
	 * @var AccountService
	 */
	private $accountService;
	/**
	 * 
	 * @var User
	 */
	private $user;

	protected function setUp()
	{
		$serviceManager = Bootstrap::getServiceManager();
		$userService = $serviceManager->get('Application\UserService');
		$this->user = $userService->findUser('60000000-0000-0000-0000-000000000000');
		$this->organizationService = $serviceManager->get('Application\OrganizationService');
		$this->accountService = $serviceManager->get('Accounting\CreditsAccountsService');
	}
	
	public function testSubscriptionProcess()
	{
		$organization = $this->organizationService->createOrganization('Lorem ipsum', $this->user);
		$this->assertNotNull($organization->getAccountId());
		
		$org = $this->organizationService->getOrganization($organization->getId());
		$this->assertNotNull($org->getAccountId(), 'The newly created organization has no account (into the read model) after creation process completed');
		
		$account = $this->accountService->findOrganizationAccount($organization->getId());
		$this->assertNotNull($account);
		$this->assertEquals($org->getAccountId()->toString(), $account->getId());
		$this->assertCount(1, $account->getHolders());
		$this->assertEquals($this->user, $account->getHolders()->first());
		$this->assertEquals(0, $account->getBalance()->getValue());
		$this->assertEmpty($account->getTransactions());
		$this->assertEquals($organization->getId()->toString(), $account->getOrganization()->getId());
	}
}
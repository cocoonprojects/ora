<?php

namespace Accounting;

use Accounting\Entity\OrganizationAccount as OrganizationAccountReadModel;
use Accounting\Service\AccountService;
use Application\Entity\User;
use IntegrationTest\Bootstrap;
use People\Service\OrganizationService;

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
		$this->organizationService = $serviceManager->get('People\OrganizationService');
		$this->accountService = $serviceManager->get('Accounting\CreditsAccountsService');
	}
	
	public function testCreationProcess()
	{
		$organization = $this->organizationService->createOrganization('Lorem ipsum', $this->user);
		$this->assertNotNull($organization->getAccountId());
		
		$org = $this->organizationService->getOrganization($organization->getId());
		$this->assertNotNull($org->getAccountId(), 'The newly created organization has no account (into the read model) after creation process completed');
		
		$account = $this->accountService->findOrganizationAccount($organization);
		$this->assertNotNull($account);
		$this->assertInstanceOf(OrganizationAccountReadModel::class, $account);
		$this->assertEquals($org->getAccountId()->toString(), $account->getId());
		$this->assertCount(1, $account->getHolders());
		$this->assertEquals($this->user, $account->getHolders()->first());
		$this->assertEquals(0, $account->getBalance()->getValue());
		$this->assertEmpty($account->getTransactions());
		$this->assertEquals($organization->getId(), $account->getOrganization()->getId());
	}
}
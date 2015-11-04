<?php

namespace Accounting;

use People\Organization;
use Zend\EventManager\ListenerAggregateInterface;
use Application\Service\UserService;
use Accounting\Service\AccountService;
use Accounting\Service\CreatePersonalAccountListener;
use Accounting\Service\AccountCommandsListener;
use IntegrationTest\Bootstrap;

class UserSubscriptionProcessTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * 
	 * @var UserService
	 */
	private $userService;
	/**
	 * 
	 * @var AccountService
	 */
	private $accountService;
	/**
	 * @var Organization
	 */
	private $organization;
	/**
	 * @var EvemtStore
	 */
	private $transactionManager;

	protected function setUp()
	{
		$serviceManager = Bootstrap::getServiceManager();
		$this->userService = $serviceManager->get('Application\UserService');
		$this->accountService = $serviceManager->get('Accounting\CreditsAccountsService');
		$organizationService = $serviceManager->get('People\OrganizationService');
		$this->organization = $organizationService->getOrganization('00000000-0000-0000-1000-000000000000');
		$this->transactionManager = $serviceManager->get('prooph.event_store');
	}
	
	public function testSubscriptionProcess()
	{
		$info = [
			'email'			=> 'john.doe@example.com',
			'family_name'	=> 'Doe',
			'given_name'	=> 'John'
		];
		$user = $this->userService->subscribeUser($info);
		$this->transactionManager->beginTransaction();
		$this->organization->addMember($user);
		$this->transactionManager->commit();
		$account = $this->accountService->findPersonalAccount($user, $this->organization);
		$this->assertNotNull($account);
		$this->assertCount(1, $account->getHolders());
		$this->assertArrayHasKey($user->getId(), $account->getHolders());
		$this->assertEquals(0, $account->getBalance()->getValue());
	}
}
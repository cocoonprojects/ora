<?php

namespace Accounting;

use Zend\EventManager\EventManager;
use Zend\EventManager\ListenerAggregateInterface;
use Ora\User\UserService;
use Accounting\Service\AccountService;
use Accounting\Service\CreatePersonalAccountListener;
use Accounting\Service\AccountCommandsListener;
use Integration\Bootstrap;

class SubscriptionProcessTest extends \PHPUnit_Framework_TestCase
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
	 * 
	 * @var EventManager
	 */
	private $events;

	protected function setUp()
	{
		$serviceManager = Bootstrap::getServiceManager();
		$this->userService = $serviceManager->get('User\UserService');
		$this->accountService = $serviceManager->get('Accounting\CreditsAccountsService');
// 		$events = $serviceManager->get('EventManager');
// 		$this->listeners[] = new CreatePersonalAccountListener($this->accountService);
// 		$this->listeners[] = new AccountCommandsListener($serviceManager->get('doctrine.entitymanager.orm_default'));
// 		array_walk($this->listeners, function ($value) use ($events) {
// 			$value->attach($events);
// 		});
// 		$this->events = $events;
	}
	
	public function testSubscriptionProcess()
	{
		$info = [
			'email'			=> 'john.doe@example.com',
			'family_name'	=> 'Doe',
			'given_name'	=> 'John'
		];
		$user = $this->userService->subscribeUser($info);
		$account = $this->accountService->findPersonalAccount($user);
		$this->assertNotNull($account);
		$this->assertCount(1, $account->getHolders());
		$this->assertEquals($user, $account->getHolders()->first());
		$this->assertEquals(0, $account->getBalance()->getValue());
		$this->assertEmpty($account->getTransactions());
	}
	
// 	protected function tearDown()
// 	{
// 		$events = $this->events;
// 		array_walk($this->listeners, function ($value) use ($events) {
// 			$value->detach($events);
// 		});
// 	}
	
}
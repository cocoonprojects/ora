<?php
namespace Ora\Accounting;

use Prooph\EventStoreTest\TestCase;
use Prooph\EventStore\Stream\SingleStreamStrategy;
use Ora\User\User;
use Prooph\EventStoreTest\Stream\SingleStreamStrategyTest;
use Prooph\EventStore\Stream\Stream;
use Prooph\EventStore\Stream\StreamName;
use Ora\Organization\Organization;
use Rhumsaa\Uuid\Uuid;

class EventSourcingAccountServiceTest extends TestCase
{
	/**
	 * 
	 * @var AccountService
	 */
	protected $accountService;
	
	protected $user;
		
	protected function setUp() {
		parent::setUp();
		$entityManager = $this->getMock('\Doctrine\ORM\EntityManager', array('getRepository', 'getClassMetadata', 'persist', 'flush'), array(), '', false);
		$eventStoreStrategy = new SingleStreamStrategy($this->eventStore);
		$this->eventStore->beginTransaction();
		$this->eventStore->create(new Stream(new StreamName('event_stream'), array()));
		$this->eventStore->commit();
		$this->accountService = new EventSourcingAccountService($this->eventStore, $entityManager);
		$this->user = User::create();
	}
	
	public function testCreatePersonalAccount() {
		$holder = $this->user;
		$account = $this->accountService->createPersonalAccount($holder);
		$this->assertInstanceOf('Ora\Accounting\Account', $account);
		$this->assertAttributeInstanceOf('Rhumsaa\Uuid\Uuid', 'id', $account);
	}
	
	public function testCreateOrganizationAccount() {
		$holder = $this->user;
		$organization = Organization::create('Test', $holder);
		$account = $this->accountService->createOrganizationAccount($holder, $organization);
		$this->assertInstanceOf('Ora\Accounting\OrganizationAccount', $account);
		$this->assertAttributeInstanceOf('Rhumsaa\Uuid\Uuid', 'id', $account);
		$a = $this->accountService->getAccount($account->getId()->toString());
		$this->assertInstanceOf('Ora\Accounting\OrganizationAccount', $a);
	}
	
	public function testDeposit() {
		$holder = $this->user;
		$organization = Organization::create('Test', $holder);
		$account = $this->accountService->createOrganizationAccount($holder, $organization);
		$balance = $account->getBalance()->getValue();
		$this->eventStore->beginTransaction();
		$account->deposit(150, $holder, "My first deposit");
		$this->eventStore->commit();
		$this->assertEquals($balance + 150, $account->getBalance()->getValue());
	}
}
<?php
namespace Accounting\Service;

use Prooph\EventStoreTest\TestCase;
use Prooph\EventStore\Stream\Stream;
use Prooph\EventStore\Stream\StreamName;
use Rhumsaa\Uuid\Uuid;
use Application\Entity\User;
use People\Organization;

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
		$this->eventStore->beginTransaction();
		$this->eventStore->create(new Stream(new StreamName('event_stream'), array()));
		$this->eventStore->commit();
		$this->accountService = new EventSourcingAccountService($this->eventStore, $entityManager);
		$this->user = User::create();
	}
	
	public function testCreatePersonalAccount() {
		$account = $this->accountService->createPersonalAccount($this->user);
		$this->assertInstanceOf('Accounting\Account', $account);
		$this->assertAttributeInstanceOf('Rhumsaa\Uuid\Uuid', 'id', $account);
	}
	
	public function testCreateOrganizationAccount() {
		$holder = $this->user;
		$organization = Organization::create('Test', $holder);
		$account = $this->accountService->createOrganizationAccount($organization, $holder);
		$this->assertInstanceOf('Accounting\OrganizationAccount', $account);
		$this->assertAttributeInstanceOf('Rhumsaa\Uuid\Uuid', 'id', $account);
		$a = $this->accountService->getAccount($account->getId()->toString());
		$this->assertInstanceOf('Accounting\OrganizationAccount', $a);
	}
	
	public function testDeposit() {
		$holder = $this->user;
		$organization = Organization::create('Test', $holder);
		$account = $this->accountService->createOrganizationAccount($organization, $holder);
		$balance = $account->getBalance()->getValue();
		$this->eventStore->beginTransaction();
		$account->deposit(150, $holder, "My first deposit");
		$this->eventStore->commit();
		$this->assertEquals($balance + 150, $account->getBalance()->getValue());
	}
}
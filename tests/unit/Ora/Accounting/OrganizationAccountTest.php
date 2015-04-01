<?php
namespace Ora\Accounting;

use Ora\User\User;
use Application\Organization;

class OrganizationAccountTest extends \PHPUnit_Framework_TestCase {
	
	private $holder;
	
	private $organization;
	
	protected function setUp() {
		$this->holder = User::create();
		$this->holder->setFirstname('John')
					 ->setLastname('Doe');
		$this->organization = Organization::create('Lorem Ipsum', $this->holder);
	}
	
	public function testCreate() {
		$account = OrganizationAccount::createOrganizationAccount($this->organization, $this->holder);
		$this->assertNotEmpty($account->getId());
		$this->assertEquals(0, $account->getBalance()->getValue());
		$this->assertArrayHasKey($this->holder->getId(), $account->getHolders());
		$this->assertEquals($this->holder->getFirstname() . ' ' . $this->holder->getLastname(), $account->getHolders()[$this->holder->getId()]);
		$this->assertEquals($this->organization->getId()->toString(), $account->getOrganizationId());
	}
}
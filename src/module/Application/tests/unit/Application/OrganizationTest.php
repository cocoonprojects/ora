<?php
namespace Application;

use Application\Entity\User;
use Accounting\OrganizationAccount;

class OrganizationTest extends \PHPUnit_Framework_TestCase {
	
	private $user;
	
	protected function setUp() {
		$this->user = User::create();
	}
	
	public function testCreate() {
		$organization = Organization::create('Donec cursus vel nisi in', $this->user);
		
		$this->assertNotNull($organization->getId());
		$this->assertEquals('Donec cursus vel nisi in', $organization->getName());
		$this->assertNull($organization->getAccountId());
		$this->assertArrayHasKey($this->user->getId(), $organization->getMembers());
		$this->assertEquals(Organization::ROLE_ADMIN, $organization->getMembers()[$this->user->getId()]['role']);
	}
	
	public function testCreateWithoutName() {
		$organization = Organization::create(null, $this->user);
		
		$this->assertNotNull($organization->getId());
		$this->assertNull($organization->getName());
		$this->assertNull($organization->getAccountId());
		$this->assertArrayHasKey($this->user->getId(), $organization->getMembers());
		$this->assertEquals(Organization::ROLE_ADMIN, $organization->getMembers()[$this->user->getId()]['role']);
	}
	
	public function testSetName() {
		$organization = Organization::create(null, $this->user);
		$organization->setName('Donec cursus vel nisi in', $this->user);
		
		$this->assertEquals('Donec cursus vel nisi in', $organization->getName());
	}
	
	public function testChangeAccount() {
		$organization = Organization::create(null, $this->user);
		$account = OrganizationAccount::createOrganizationAccount($organization, $this->user);
		
		$this->assertEquals($account->getId()->toString(), $organization->getAccountId());
	}
	
	public function testAddMember() {
		$organization = Organization::create(null, $this->user);
		$u = User::create();
		$organization->addMember($u, $this->user);
		
		$this->assertArrayHasKey($u->getId(), $organization->getMembers());
		$this->assertEquals(Organization::ROLE_MEMBER, $organization->getMembers()[$u->getId()]['role']);
	}
	
	public function testAddMemberAsAdmin() {
		$organization = Organization::create(null, $this->user);
		$u = User::create();
		$organization->addMember($u, $this->user, Organization::ROLE_ADMIN);
		
		$this->assertArrayHasKey($u->getId(), $organization->getMembers());
		$this->assertEquals(Organization::ROLE_ADMIN, $organization->getMembers()[$u->getId()]['role']);
	}
	
	/**
	 * @expectedException Application\DuplicatedDomainEntityException
	 */
	public function testReaddMember() {
		$organization = Organization::create(null, $this->user);
		$organization->addMember($this->user, $this->user);
	}
	
	public function testRemoveMember() {
		$organization = Organization::create(null, $this->user);
		$u = User::create();
		$organization->addMember($u, $this->user);
		$organization->removeMember($u, $this->user);

		$this->assertArrayNotHasKey($u->getId(), $organization->getMembers());
	}
	
	/**
	 * @expectedException Application\DomainEntityUnavailableException
	 */
	public function testRemoveANonMember() {
		$organization = Organization::create(null, $this->user);
		$u = User::create();
		$organization->removeMember($u, $this->user);
	}
}
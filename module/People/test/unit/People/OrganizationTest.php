<?php
namespace People;

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
		$account = OrganizationAccount::create($organization, $this->user);
		
		$this->assertEquals($account->getId(), $organization->getAccountId());
	}
	
	public function testAddContributor() {
		$organization = Organization::create(null, $this->user);
		$u = User::create();
		$organization->addMember($u);
		
		$this->assertArrayHasKey($u->getId(), $organization->getMembers());
		$this->assertEquals(Organization::ROLE_CONTRIBUTOR, $organization->getMembers()[$u->getId()]['role']);
	}
	
	public function testAddMember() {
		$organization = Organization::create(null, $this->user);
		$u = User::create();
		$organization->addMember($u, Organization::ROLE_MEMBER);
		
		$this->assertArrayHasKey($u->getId(), $organization->getMembers());
		$this->assertEquals(Organization::ROLE_MEMBER, $organization->getMembers()[$u->getId()]['role']);
	}
	
	public function testAddMemberAsAdmin() {
		$organization = Organization::create(null, $this->user);
		$u = User::create();
		$organization->addMember($u, Organization::ROLE_ADMIN);
		
		$this->assertArrayHasKey($u->getId(), $organization->getMembers());
		$this->assertEquals(Organization::ROLE_ADMIN, $organization->getMembers()[$u->getId()]['role']);
	}
	
	public function testPromoteContributorToMember() {
		$organization = Organization::create(null, $this->user);
		$u = User::create();
		$organization->addMember($u, Organization::ROLE_CONTRIBUTOR);
		$organization->promoteMember($u, Organization::ROLE_MEMBER);

		$this->assertArrayHasKey($u->getId(), $organization->getMembers());
		$this->assertEquals(Organization::ROLE_MEMBER, $organization->getMembers()[$u->getId()]['role']);
	}

	/**
	 * @expectedException Application\DuplicatedDomainEntityException
	 */
	public function testReaddMember() {
		$organization = Organization::create(null, $this->user);
		$organization->addMember($this->user);
	}
	
	public function testRemoveMember() {
		$organization = Organization::create(null, $this->user);
		$u = User::create();
		$organization->addMember($u);
		$organization->removeMember($u);

		$this->assertArrayNotHasKey($u->getId(), $organization->getMembers());
	}
	
	/**
	 * @expectedException Application\DomainEntityUnavailableException
	 */
	public function testRemoveANonMember() {
		$organization = Organization::create(null, $this->user);
		$u = User::create();
		$organization->removeMember($u);
	}

	public function testGetAdminsAfterCreation() {
		$organization = Organization::create(null, $this->user);
		$this->assertCount(1, $organization->getAdmins());
	}

	public function testGetAdminsAfterMemberAdded() {
		$organization = Organization::create(null, $this->user);
		$u = User::create();
		$organization->addMember($u, $this->user);
		$this->assertCount(1, $organization->getAdmins());
		$this->assertArrayHasKey($this->user->getId(), $organization->getAdmins());
	}

	public function testGetAdminsAfterAdminAdded() {
		$organization = Organization::create(null, $this->user);
		$u = User::create();
		$organization->addMember($u, Organization::ROLE_ADMIN);
		$this->assertCount(2, $organization->getAdmins());
		$this->assertArrayHasKey($u->getId(), $organization->getAdmins());
	}
}
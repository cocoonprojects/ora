<?php
namespace People\Service;

use Prooph\EventStoreTest\TestCase;
use Prooph\EventStore\Stream\Stream;
use Prooph\EventStore\Stream\StreamName;
use Application\Entity\User;

class EventSourcingOrganizationServiceTest extends TestCase {
	
	/**
	 * 
	 * @var OrganizationService
	 */
	private $organizationService;
	/**
	 * 
	 * @var User
	 */
	private $user;
	
	protected function setUp() {
		parent::setUp();
		$entityManager = $this->getMock('\Doctrine\ORM\EntityManager', array('getRepository', 'getClassMetadata', 'persist', 'flush'), array(), '', false);
		$this->eventStore->beginTransaction();
		$this->eventStore->create(new Stream(new StreamName('event_stream'), array()));
		$this->eventStore->commit();
		$this->organizationService = new EventSourcingOrganizationService($this->eventStore, $entityManager);
		$this->user = User::create();
	}
	
	public function testCreateOrganization() {
		$organization = $this->organizationService->createOrganization('Donec cursus vel nisi in', $this->user);
		
		$this->assertAttributeInstanceOf('Rhumsaa\Uuid\Uuid', 'id', $organization);
		$this->assertAttributeEquals('Donec cursus vel nisi in', 'name', $organization);
	}

	public function testCreateOrganizationWithoutName() {
		$organization = $this->organizationService->createOrganization(null, $this->user);
		
		$this->assertAttributeInstanceOf('Rhumsaa\Uuid\Uuid', 'id', $organization);
		$this->assertNull($organization->getName());
	}
}
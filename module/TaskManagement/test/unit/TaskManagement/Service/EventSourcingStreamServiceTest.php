<?php
namespace TaskManagement\Service;

use Prooph\EventStoreTest\TestCase;
use Prooph\EventStore\Stream\Stream as ProophStream;
use Prooph\EventStore\Stream\StreamName;
use Rhumsaa\Uuid\Uuid;
use Application\Entity\User;
use People\Organization;

class EventSourcingStreamServiceTest extends TestCase {
	
	/**
	 * 
	 * @var StreamService
	 */
	private $streamService;
	/**
	 * 
	 * @var User
	 */
	private $user;
	
	protected function setUp() {
		parent::setUp();
		$entityManager = $this->getMock('\Doctrine\ORM\EntityManager', array('getRepository', 'getClassMetadata', 'persist', 'flush'), array(), '', false);
		$this->eventStore->beginTransaction();
		$this->eventStore->create(new ProophStream(new StreamName('event_stream'), array()));
		$this->eventStore->commit();
		$this->streamService = new EventSourcingStreamService($this->eventStore, $entityManager);
		$this->user = User::create();
	}
	
	public function testCreate() {
		$organization = Organization::create('Quisque quis tortor ligula. Duis', $this->user);
		$stream = $this->streamService->createStream($organization, 'Mauris vel lectus pellentesque, cursus', $this->user);
		$this->assertInstanceOf('Rhumsaa\Uuid\Uuid', $stream->getId());
		$this->assertEquals('Mauris vel lectus pellentesque, cursus', $stream->getSubject());
		$this->assertEquals($organization->getId()->toString(), $stream->getOrganizationId());
	}
}

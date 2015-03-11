<?php
namespace Ora\StreamManagement;

use Ora\User\User;
use Ora\Organization\Organization;

class StreamTest extends \PHPUnit_Framework_TestCase {
	
	private $organization;
	
	private $user;
	
	protected function setUp() {
		$this->user = User::create();
		$this->organization = Organization::create('Pellentesque consequat lacinia arcu vitae', $this->user);
	}
	
	public function testCreate() {
		$stream = Stream::create($this->organization, 'Duis vulputate nulla vel purus', $this->user);
		
		$this->assertNotEmpty($stream->getId());
		$this->assertEquals('Duis vulputate nulla vel purus', $stream->getSubject());
		$this->assertEquals($this->organization->getId()->toString(), $stream->getOrganizationId());
	}

	public function testCreateWithNoSubject() {
		$stream = Stream::create($this->organization, null, $this->user);
		
		$this->assertNotEmpty($stream->getId());
		$this->assertNull($stream->getSubject());
		$this->assertEquals($this->organization->getId()->toString(), $stream->getOrganizationId());
	}
	
	public function testSetSubject() {
		$stream = Stream::create($this->organization, null, $this->user);
		$stream->setSubject('Duis vulputate nulla vel purus', $this->user);
		
		$this->assertEquals('Duis vulputate nulla vel purus', $stream->getSubject());
	}
	
	public function testChangeOrganization() {
		$stream = Stream::create($this->organization, 'Duis vulputate nulla vel purus', $this->user);
		$org = Organization::create('Fusce nec fringilla turpis. Donec', $this->user);
		$stream->changeOrganization($org, $this->user);
		
		$this->assertEquals($org->getId()->toString(), $stream->getOrganizationId());
	}
}
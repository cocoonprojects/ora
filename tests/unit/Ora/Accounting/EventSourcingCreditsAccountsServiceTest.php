<?php
namespace Ora\Accounting;

use Prooph\EventStoreTest\TestCase;

class EventSourcingCreditsAccountsServiceTest extends TestCase
{
	/**
	 * 
	 * @var CreditsAccountsService
	 */
	protected $creditsAccountsService;
	
	protected function setUp() {
		parent::setUp();
		$eventStoreStrategy = new SingleStreamStrategy($this->eventStore);
		$this->creditsAccountsService = new EventSourcingCreditsAccountsService($this->eventStore, $eventStoreStrategy);
	}
	
	public function testCreate() {
		
	}
	
// 	protected function tearDown() {
// 		parent::tearDown();
// 	}
	
}
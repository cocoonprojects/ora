<?php
namespace Ora\Accounting;

class CreditsAccountTest extends \PHPUnit_Framework_TestCase
{
	public function testCreateNow() {
		$account = CreditsAccount::create();
		$this->assertNotEmpty($account->getId());
	}
	
	public function testCreateInThePast() {
		
	}
	
	public function testCreateInTheFuture() {
		
	}
}

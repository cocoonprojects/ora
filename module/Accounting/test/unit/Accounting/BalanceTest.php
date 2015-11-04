<?php

namespace Accounting;

class BalanceTest extends \PHPUnit_Framework_TestCase
{
	public function testBalance()
	{
		$now = new \DateTime();
		$balance = new Balance(300, $now);
		$this->assertEquals(300, $balance->getValue());
		$this->assertEquals($now, $balance->getDate());
	}
}
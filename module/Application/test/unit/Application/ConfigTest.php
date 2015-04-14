<?php
namespace Application;

class ConfigTest extends \PHPUnit_Framework_TestCase {
	
	public function testShareAssignmentTimeboxValue(){
		
		$returnArray = include (__DIR__.'/../../../../../config/autoload/global.php');		
		$expected = new \DateInterval('P7D');		
		$this->assertEquals($expected, $returnArray['share_assignment_timebox']);		
	}	
}
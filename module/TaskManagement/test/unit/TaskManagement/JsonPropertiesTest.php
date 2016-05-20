<?php
namespace TaskManagement;

use Application\Entity\User;

class JsonPropertiesTest extends \PHPUnit_Framework_TestCase {
	
	protected $json;

	protected function setUp() {
		$this->json = <<<'EOT'
		{
		    "_links": {
		        "self": {
		            "href": "/00000000-0000-0000-1000-000000000000/task-management/tasks"
		        },
		        "ora:create": {
		            "href": "/00000000-0000-0000-1000-000000000000/task-management/tasks"
		        },
		        "next": {
		            "href": "/00000000-0000-0000-1000-000000000000/task-management/tasks"
		        }
		    },
		    "_embedded": {
		        "ora:task": [
		            {
		                "id": "00000000-0000-0000-0000-000000000004",
		                "subject": "Development environment setup",
		                "description": "Development environment setup"
					}
				]
			}
		}
EOT;
	}


	protected function getJsonProperties()
	{
		//if(is_null($this->jsonProperties)) 
		{
			$json = json_decode($this->json);
			$iterator = new \RecursiveIteratorIterator(new \RecursiveArrayIterator($json), \RecursiveIteratorIterator::SELF_FIRST);
			$this->jsonProperties = array();
			foreach ($iterator as $key => $value) {
				// if (strpos($key, ':'))
				// 	$key = preg_replace('/([\w])/', '[${1}]', $key)
				// 	// $key = '{'.$key.'}';

				// Build long key name based on parent keys
				for ($i = $iterator->getDepth() - 1; $i >= 0; $i--) {

					$firstKeyChunk = $iterator->getSubIterator($i)->key();

					if (strpos($firstKeyChunk, ':')) {
					 	$firstKeyChunk = '{'.$firstKeyChunk.'}';
					}

					if (preg_match('/(^[\d]+)/', $key))
						$key = $firstKeyChunk . preg_replace('/(^\d+)/', '[${1}]', $key);
					else
						$key = $firstKeyChunk . '.' . $key;

//var_dump($firstKeyChunk);					
				}
				$this->jsonProperties[] = $key;
			}
		}
		return $this->jsonProperties;
	}


	public function testParseJson() {

		$properties = $this->getJsonProperties();

		$this->assertNotEmpty($properties);
	}
}
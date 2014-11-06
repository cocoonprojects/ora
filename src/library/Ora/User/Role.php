<?php
namespace Ora\User;

use Doctrine\ORM\Mapping AS ORM;

/**
 * @ORM\Embeddable
 */

class Role {
	
	const ROLE_USER = 'User';
	
	private static $roles = array(self::ROLE_USER);
	private static $instances = array();
	
	/**
	 *  @ORM\Column(type="string")
	*/
    private $name;
	 
	public function getName() {
		return $this->name;
	}
	
	public static function instance($name) {
		
		if(!in_array($name, self::$roles)) {
			return null;
		}
		
		$rv = null;
		//$rv = self::$instances[$name];
		
		if(!in_array($name, self::$instances)) {
			$rv = new Role($name);
			self::$instances[$name] = $rv;
		}
		
		return $rv;
	}
	
	private function __construct($name) {
		$this->name = $name;
	}
}


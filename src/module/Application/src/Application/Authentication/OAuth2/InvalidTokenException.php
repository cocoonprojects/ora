<?php
namespace Application\Authentication\OAuth2;

class InvalidTokenException extends \Exception {
	
	public function __construct($msg) {
		parent::__construct($msg);
	}
}
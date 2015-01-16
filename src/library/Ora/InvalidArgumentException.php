<?php
namespace Ora;

class InvalidArgumentException extends \DomainException {
	
	public function __construct($msg) {
		parent::__construct($msg);
	}
}
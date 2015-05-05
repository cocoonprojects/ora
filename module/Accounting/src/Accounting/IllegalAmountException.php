<?php
namespace Accounting;

class IllegalAmountException extends \DomainException {

	public function __construct($amount) {
		parent::__construct('Illegal amount '.$amount);
	}
}

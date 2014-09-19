<?php
namespace Ora\Accounting;

class UnsupportedChangeException extends \Exception {
	
	public function __construct($fromCurrency, $toCurrency) {
		parent::__constructu('Unsupported change from '.$fromCurrency.' to '.$toCurrency);
	}
}
<?php
namespace Ora\Accounting;

use Prooph\EventSourcing\AggregateChanged;

/**
 * 
 * @author andreabandera
 *
 */
class CreditsAccountEvent extends AggregateChanged {

	public function getCurrency() {
		return $this->toPayloadReader()->stringValue('currency');
	}
	
	public function getBalance() {
		$value = $this->toPayloadReader()->floatValue('balanceValue');
		$date = $this->toPayloadReader()->stringValue('balanceDate');
		$d = date_create_from_format('Y-m-d H:i:s', $date);
		return new Balance($value, $d);
	}
}
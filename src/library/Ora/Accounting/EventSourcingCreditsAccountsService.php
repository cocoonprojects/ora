<?php
namespace Ora\Accounting;

use Ora\EventStore\EventStore;
use \DateTime;

class EventSourcingCreditsAccountsService implements CreditsAccountsService {
	
	/**
	 * @var EventStore
	 */
	private $es;
	
	public function __construct(EventStore $es) {
		$this->es = $es;
	}
	
	public function create($currency = null) {
		$accountId = uniqid();
		$curr = isset($currency) ? $currency : 'CUN';
		$account = new CreditsAccount($accountId, new \DateTime(), $this->es, $currency);
		$e = new CreditsAccountCreated(new DateTime(), $account);
		$this->es->appendToStream($e);
	}
	
	public function listAccounts() {
		$a = new CreditsAccount('123458', new \DateTime(), $this->es, 'CUN');
		$a->setBalance(new Balance(1500, new \DateTime()));
		$b = new CreditsAccount('200060', new \DateTime(), $this->es, 'CUN');
		$b->setBalance(new Balance(1500, new \DateTime()));
		return array($a, $b);
	}
	
	public function getAccount($id) {
		$rv = new CreditsAccount($id, new \DateTime(), $this->es);
		$rv->setBalance(new Balance(1500, new \DateTime()));
		return $rv;
	}
	
	public function transfer(CreditsAccount $source, CreditsAccount $destination, $value, \DateTime $when) {
		try {
			$source->withdraw($value, $when);
			$destination->deposit($value, $when);
		} catch (Exception $e) {
			
		}
	}

}
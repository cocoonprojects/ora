<?php
namespace ZFX\EventStore\Controller\Plugin;

use Zend\Mvc\Controller\Plugin\AbstractPlugin;
use Prooph\EventStore\EventStore;

class EventStoreTransactionPlugin extends AbstractPlugin {
	
	/**
	 * 
	 * @var EventStore
	 */
	private $transactionManager;
	
	public function __construct(EventStore $transactionManager) {
		$this->transactionManager = $transactionManager;
	}
	
	public function begin() {
		$this->transactionManager->beginTransaction();
	}
	
	public function rollback() {
		$this->transactionManager->rollback();
	}
	
	public function commit() {
		$this->transactionManager->commit();
	}
}
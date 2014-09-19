<?php
namespace Ora\EventStore;

use Ora\DomainEvent;

interface EventStore {
	
	public function appendToStream(DomainEvent $e);
	
}
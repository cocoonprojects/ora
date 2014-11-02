<?php

namespace Ora\TaskManagement;

use Prooph\EventSourcing\AggregateChanged;
use Ora\DomainEvent;

/**
* 
* @author Giannotti Fabio
*/
class TaskCreated extends AggregateChanged implements DomainEvent
{
	public function getEntity() {
		return $this->payload['task'];
	}
}
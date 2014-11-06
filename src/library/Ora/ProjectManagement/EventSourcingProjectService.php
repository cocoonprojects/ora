<?php

namespace Ora\ProjectManagement;

use Prooph\EventStore\Aggregate\AggregateRepository;
use Prooph\EventStore\Aggregate\AggregateType;
use Prooph\EventStore\EventStore;
use Prooph\EventStore\Stream\StreamStrategyInterface;
use Prooph\EventSourcing\EventStoreIntegration\AggregateTranslator;
use Ora\User\User;

/**
 * @author Giannotti Fabio
 */
class EventSourcingProjectService extends AggregateRepository implements ProjectService
{
	public function __construct(EventStore $eventStore, StreamStrategyInterface $eventStoreStrategy) {
		parent::__construct($eventStore, new AggregateTranslator(), $eventStoreStrategy, new AggregateType('Ora\ProjectManagement\Project'));
	}
    	
	public function getProject($id)
	{
		try {
		    $project = $this->getAggregateRoot($this->aggregateType, $id);
		    return $project;
		} catch (\RuntimeException $e) {
			return null;
		}
	}
} 
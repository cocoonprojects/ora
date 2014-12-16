<?php

namespace Ora\StreamManagement;

use Prooph\EventStore\Aggregate\AggregateRepository;
use Prooph\EventStore\Aggregate\AggregateType;
use Prooph\EventStore\EventStore;
use Prooph\EventStore\Stream\StreamStrategyInterface;
use Prooph\EventSourcing\EventStoreIntegration\AggregateTranslator;
use Ora\User\User;
use Ora\ReadModel\Organization;

/**
 * @author Giannotti Fabio
 */
class EventSourcingStreamService extends AggregateRepository implements StreamService
{
	public function __construct(EventStore $eventStore, StreamStrategyInterface $eventStoreStrategy) {
		parent::__construct($eventStore, new AggregateTranslator(), $eventStoreStrategy, new AggregateType('Ora\StreamManagement\Stream'));
	}
    	
	public function getStream($id)
	{
		try {
		    $stream = $this->getAggregateRoot($this->aggregateType, $id);
		    return $stream;
		} catch (\RuntimeException $e) {
			return null;
		}
	}
	
	public function findOrganizationStreams(Organization $organization)
	{
		$streams = $this->entityManager
						     ->getRepository('Ora\ReadModel\Stream')
							 ->findBy(array("organization" => $organization));
		
		return $streams;		
	}
} 
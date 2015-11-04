<?php
namespace Application\Service;

use People\OrganizationMemberAdded;
use Zend\EventManager\ListenerAggregateInterface;
use Zend\EventManager\EventManagerInterface;
use Prooph\EventStore\PersistenceEvent\PostCommitEvent;

class DomainEventDispatcher implements ListenerAggregateInterface
{
	protected $listeners = array();

	public function attach(EventManagerInterface $events) {
		$that = $this;
		$this->listeners[] = $events->getSharedManager()->attach('prooph_event_store', 'commit.post',
			function(PostCommitEvent $event) use ($that, $events) {
				foreach ($event->getRecordedEvents() as $streamEvent) {
					$eventName = $streamEvent->eventName();
					$events->trigger($eventName->toString(), $streamEvent, $streamEvent->payload());
				}
			}); // Execute business processes after read model update
	}
	
	public function detach(EventManagerInterface $events)
	{
		if ($events->getSharedManager()->detach('prooph_event_store', $this->listeners[0])) {
			unset($this->listeners[0]);
		}
	}
}
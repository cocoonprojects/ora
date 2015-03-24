<?php
namespace Ora\Service;

use Doctrine\ORM\EntityManager;
use Prooph\EventStore\PersistenceEvent\PostCommitEvent;
use Prooph\EventStore\Stream\StreamEvent;
use Zend\EventManager\ListenerAggregateInterface;
use Zend\EventManager\EventManagerInterface;

abstract class SyncReadModelListener  implements ListenerAggregateInterface
{
	protected $listeners = array();
	
	/**
	 * 
	 * @var EntityManager
	 */
	protected $entityManager;
	
	public function __construct(EntityManager $entityManager) {
		$this->entityManager = $entityManager;
	}
	
	public function attach(EventManagerInterface $events) {
		$that = $this;
		$this->listeners[] = $events->getSharedManager()->attach('prooph_event_store', 'commit.post', function(PostCommitEvent $event) use ($that) {
			foreach ($event->getRecordedEvents() as $streamEvent) {
				$handler = $that->getHandler($streamEvent);
				if (is_null($handler)) {
					continue;
				}
				$that->{$handler}($streamEvent);				
			}
	 		$that->entityManager->flush();
		});
		$this->events = $events;
	}
	
    public function detach(EventManagerInterface $events)
    {
        foreach ($this->listeners as $index => $listener) {
            if ($events->detach($listener)) {
                unset($this->listeners[$index]);
            }
        }
    }
	
	public function getHandler(StreamEvent $event) {
		$needle = $this->getPackage();
		$length = strlen($needle);
		$haystack = $event->eventName();
		if(substr($haystack, 0, $length) === $needle) {
			$handler = 'on' . join('', array_slice(explode('\\', $event->eventName()), -1));
			if(method_exists($this, $handler)) {
				return $handler;
			}
		}
		return null;
	}
	
	protected abstract function getPackage();
}
<?php
namespace Ora\Service;

use Doctrine\ORM\EntityManager;
use Prooph\EventStore\EventStore;
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
	
	public function observe(EventStore $eventStore) {
		$eventStore->getPersistenceEvents()->attach('commit.post', array($this, 'postCommit'));
	}
	
	public function postCommit(PostCommitEvent $event) {
		foreach ($event->getRecordedEvents() as $streamEvent) {
			$handler = $this->getHandler($streamEvent);
			if (is_null($handler)) {
				continue;
			}
			$this->{$handler}($streamEvent);				
		}
 		$this->entityManager->flush();
	}
	
	public function attach(EventManagerInterface $events) {
		$this->listeners[] = $events->getSharedManager()->attach('prooph_event_store', User::EVENT_CREATED, function(Event $event) use ($accountService) {
			$user = $event->getTarget();
			$this->accountService->createPersonalAccount($user);
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
	
	protected function getHandler(StreamEvent $event) {
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
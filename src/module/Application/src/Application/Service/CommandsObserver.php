<?php
namespace Application\Service;

use Doctrine\ORM\EntityManager;
use Prooph\EventStore\EventStore;
use Prooph\EventStore\PersistenceEvent\PostCommitEvent;
use Prooph\EventStore\Stream\StreamEvent;

abstract class CommandsObserver
{
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
<?php
namespace Ora\EventStore;

use Ora\DomainEvent;
use Doctrine\ORM\EntityManager;

class DoctrineEventStore implements EventStore {
	
	/**
	 * 
	 * @var EntityManager
	 */
	private $entityManager;
	
	public function __construct(EntityManager $em) {
		$this->entityManager = $em;
	}

	public function appendToStream(DomainEvent $e) {
		$this->entityManager->persist($e);
		$this->entityManager->flush();
	}
	
}
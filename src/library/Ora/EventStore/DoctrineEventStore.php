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
	
	public function __construct(EntityManager $entityManager) 
	{
		$this->entityManager = $entityManager;
	}
	
	public function appendToStream(DomainEvent $domainEvent) 
	{
		$this->entityManager->persist($domainEvent);
		$this->entityManager->flush($domainEvent);
	}
	
}
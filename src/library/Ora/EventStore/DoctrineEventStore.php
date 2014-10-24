<?php

namespace Ora\EventStore;

use Ora\DomainEvent;
use Doctrine\ORM\EntityManager;

class DoctrineEventStore implements EventStore 
{	
	private static $instance;
	
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
	
	public static function instance(EntityManager $entityManager) 
	{
		if(is_null(self::$instance))
			self::$instance = new DoctrineEventStore($entityManager);
        
		return self::$instance;
	}
}
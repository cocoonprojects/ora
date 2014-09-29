<?php
namespace Ora\EventStore;

use Ora\DomainEvent;
use Doctrine\ORM\EntityManager;

class DoctrineEventStore implements EventStore {
	
	private static $instance;
	
	/**
	 * 
	 * @var EntityManager
	 */
	private $entityManager;
	
	private function __construct(EntityManager $em) {
		$this->entityManager = $em;
	}

	public function appendToStream(DomainEvent $e) {
		$this->entityManager->persist($e);
		$this->entityManager->flush();
	}
	
	public static function instance(EntityManager $em) {
		if(is_null(self::$instance)) {
			self::$instance = new DoctrineEventStore($em);
		}
		return self::$instance;
	}
}
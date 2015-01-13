<?php
namespace Ora\StreamManagement;

use Rhumsaa\Uuid\Uuid;

class MockStreamService implements StreamService {
	
	private $entityManager;
	
	public function __construct($entityManager)
	{
		$this->entityManager = $entityManager;
	}
	
	public function getStream($id) {
		$s = $this->findStream($id);
		if(is_null($s)) {
			return null;
		}
		$rv = new Stream(Uuid::fromString($s->getId()), $s->getCreatedBy());
		$rv->setSubject($s->getSubject());
		return $rv;
	}
	
	public function findStream($id)
	{
		return $this->entityManager->find('Ora\ReadModel\Stream', $id);
	}
}
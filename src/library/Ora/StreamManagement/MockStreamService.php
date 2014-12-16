<?php
namespace Ora\StreamManagement;

use Rhumsaa\Uuid\Uuid;
use Ora\User\User;
use Ora\User\UserService;
use Ora\ReadModel\Organization;

class MockStreamService implements StreamService {
	
	/**
	 * 
	 * @var UserService
	 */
	private $userService;
	private $entityManager;
	
	public function __construct(UserService $userService, $entityManager)
	{
		$this->userService = $userService;
		$this->entityManager = $entityManager;
	}
	
	public function getStream($id) {
		try {
			$streamId = Uuid::fromString($id);
			$user = $this->userService->findUser('20000000-0000-0000-0000-000000000000');
			$rv = new Stream($streamId, $user);
			$rv->setSubject('First stream');
			return $rv;
		} catch(\InvalidArgumentException $e) {
			return null;
		}
	}
	
	public function findOrganizationStreams(Organization $organization)
	{
		$streams = $this->entityManager
					     ->getRepository('Ora\ReadModel\Stream')
					     ->findBy(array('id' => '00000000-1000-0000-0000-000000000000'));
					     		
		return $streams;		
	}
}
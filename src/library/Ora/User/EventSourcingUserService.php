<?php

namespace Ora\User;

use Ora\EventStore\EventStore;
use Ora\EntitySerializer;
use Ora\User\Role;

class EventSourcingUserService implements UserService
{
	private $entityManager;
	private $eventStore;
	private $entitySerializer;
	
	public function __construct($entityManager, EventStore $eventStore, EntitySerializer $entitySerializer, $authenticationService)
	{
		$this->entityManager = $entityManager;
		$this->eventStore = $eventStore;
		$this->entitySerializer = $entitySerializer;
		$this->authenticationService = $authenticationService;
	}
	
	public function subscribeUser($infoOfUser)
	{
		$user = $this->create($infoOfUser, Role::instance(Role::ROLE_USER));
			
		$event = new UserSubscribedEvent(new \DateTime(), $user, $this->entitySerializer);
				
		$this->eventStore->appendToStream($event);
				
		return $user;			
	}
		
	public function create($infoOfUser, Role $role)
	{
		$userID = uniqid();
		$createdAt = new \DateTime();
		$createdBy = $userID;
	   
		if($this->authenticationService->hasIdentity())
		{
			$user = $this->authenticationService->getIdentity();
	
			$createdBy = $user->getId();
		}
	
		$user = new User($userID, $createdAt, $createdBy);
	
		$user->setEmail($infoOfUser['email']);
		$user->setLastname($infoOfUser['lastname']);
		$user->setFirstname($infoOfUser['firstname']);
		$user->setSystemRole($role);
	
		return $user;
	}	

	
}

?>

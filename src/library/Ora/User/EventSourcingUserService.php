<?php

namespace Ora\User;

use Ora\EventStore\EventStore;
use Ora\EntitySerializer;

use Ora\User\Role;
//use Ora\Organization\Organization;
use Ora\UserOrganization\UserOrganization;
use Ora\User\User;

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
		
	//public function create($infoOfUser, Role $role, Organization $organization)
	public function create($infoOfUser, Role $role)
	{
		$userID = uniqid();
		$createdAt = new \DateTime();
		$createdBy = null;
	   
		if($this->authenticationService->hasIdentity())
		{
			$user = $this->authenticationService->getIdentity();	
			$createdBy = $user;
		}
	
		$user = new User($userID, $createdAt, $createdBy);
	
		$user->setEmail($infoOfUser['email']);
		$user->setLastname($infoOfUser['family_name']);
		$user->setFirstname($infoOfUser['given_name']);
		$user->setSystemRole($role);
					
		return $user;
	}

	public function findUser($id)
	{
		$user = $this->entityManager
				     ->getRepository('Ora\User\User')
		             ->findOneBy(array("id" => $id));
		 
		return $user;		
	}
	
	public function findUserByEmail($mail)
	{
		$user = $this->entityManager
					->getRepository('Ora\User\User')
					->findOneBy(array("email" => $email));
			
		return $user;		
	}
}

?>

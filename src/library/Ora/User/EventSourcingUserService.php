<?php

namespace Ora\User;

use Doctrine\ORM\EntityManager;
use Prooph\EventStore\EventStore;
use Prooph\EventStore\Stream\StreamStrategyInterface;
use Prooph\EventStore\Aggregate\AggregateRepository;
use Prooph\EventSourcing\EventStoreIntegration\AggregateTranslator;
use Prooph\EventStore\Aggregate\AggregateType;
use Ora\User\Role;
use Ora\Organization\Organization;
use Ora\User\User;

class EventSourcingUserService extends AggregateRepository implements UserService
{
	private $entityManager;
	
	public function __construct(EventStore $eventStore, StreamStrategyInterface $eventStoreStrategy, EntityManager $entityManager)
	{
		parent::__construct($eventStore, new AggregateTranslator(), $eventStoreStrategy, new AggregateType('Ora\User\User'));
		$this->entityManager = $entityManager;
	}
	
	public function subscribeUser($infoOfUser)
	{
		$user = $this->create($infoOfUser, Role::instance(Role::ROLE_USER));
		$this->entityManager->persist($user);			
		$this->entityManager->flush($user);
		return $user;			
	}
		
	public function create($infoOfUser, Role $role, User $createdBy = null)
	{	
		$user = User::create($createdBy);
		$user->setEmail($infoOfUser['email']);
		$user->setLastname($infoOfUser['family_name']);
		$user->setFirstname($infoOfUser['given_name']);
		return $user;
	}

	public function findUser($id)
	{
		$user = $this->entityManager
				     ->getRepository('Ora\User\User')
		             ->findOneBy(array("id" => $id));
		 
		return $user;		
	}
	
	public function findUserByEmail($email)
	{
		$user = $this->entityManager
					->getRepository('Ora\User\User')
					->findOneBy(array("email" => $email));
			
		return $user;		
	}
}
?>

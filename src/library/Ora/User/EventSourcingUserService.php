<?php
namespace Ora\User;

use Zend\EventManager\EventManagerAwareInterface;
use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\EventManager;
use Doctrine\ORM\EntityManager;
use Ora\Organization\Organization;
use Ora\User\User;

class EventSourcingUserService implements UserService, EventManagerAwareInterface
{
	/**
	 * 
	 * @var EventManagerInterface
	 */
	protected $events;
	/**
	 * 
	 * @var EntityManager
	 */
	private $entityManager;
	
	public function __construct(EntityManager $entityManager)
	{
		$this->entityManager = $entityManager;
	}
	
	public function subscribeUser($infoOfUser)
	{
		$user = $this->create($infoOfUser, User::ROLE_USER);
		$this->entityManager->persist($user);			
		$this->entityManager->flush($user);
		$this->getEventManager()->trigger(User::EVENT_CREATED, $user);
		return $user;			
	}
		
	public function create($infoOfUser, $role, User $createdBy = null)
	{	
		$user = User::create($createdBy);
		$user->setEmail($infoOfUser['email']);
		$user->setLastname($infoOfUser['family_name']);
		$user->setFirstname($infoOfUser['given_name']);
		$user->setPicture($infoOfUser['picture']);
		$user->setRole($role);
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

	public function setEventManager(EventManagerInterface $events)
	{
		$events->setIdentifiers(array(
			__CLASS__,
			get_class($this)
		));
		$this->events = $events;
	}

	public function getEventManager()
	{
		if (!$this->events) {
			$this->setEventManager(new EventManager());
		}
		return $this->events;
	}
}
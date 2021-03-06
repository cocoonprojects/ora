<?php
namespace Application\Service;

use Zend\EventManager\EventManagerAwareInterface;
use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\EventManager;
use Doctrine\ORM\EntityManager;
use Application\Entity\User;

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
	
	public function subscribeUser($userInfo)
	{
		$user = $this->create($userInfo, User::ROLE_USER);
		return $user;
	}

	public function create($userInfo, $role, User $createdBy = null)
	{
		$user = User::create($createdBy);
		$user->setEmail($userInfo['email']);
		$user->setLastname($userInfo['family_name']);
		$user->setFirstname($userInfo['given_name']);
		if(isset($userInfo['picture'])) {
			$user->setPicture($userInfo['picture']);
		}
		$user->setRole($role);
		$this->entityManager->persist($user);
		$this->entityManager->flush($user);
		$this->getEventManager()->trigger(User::EVENT_CREATED, $user);
		return $user;
	}

	public function findUser($id)
	{
		$user = $this->entityManager
					 ->getRepository(User::class)
					 ->findOneBy(array("id" => $id));
		return $user;
	}
	
	public function findUserByEmail($email)
	{
		$user = $this->entityManager
					->getRepository(User::class)
					->findOneBy(array("email" => $email));
		return $user;
	}	

	public function findUsers($filters){
		$builder = $this->entityManager->createQueryBuilder();
		$query = $builder->select('u')
			->from(User::class, 'u')
			->orderBy('u.mostRecentEditAt', 'DESC');
		if(isset($filters["kanbanizeusername"])){
			$query->andWhere('u. kanbanizeUsername = :username')
				->setParameter('username', $filters["kanbanizeusername"]);
		}
		return $query->getQuery()->getResult();
	}
	
	public function setEventManager(EventManagerInterface $events)
	{
		$events->setIdentifiers(array(
			'Application\UserService',
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
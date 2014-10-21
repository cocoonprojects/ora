<?php 

namespace Ora\User;

class UserService
{
	private $entityManager;
	
	public function __construct($entityManager)
	{
		$this->entityManager = $entityManager;
	}	
	
	/**
	 * User Subscribe to the system the first time user log in with supported SSO
	 * 
	 * @param array [email, lastname, firstname]
	 */
	public function createNewUser($identity)
	{		
		// TODO: Utente system? o user per SSO
		$user = $this->findOneByEmail($identity['email']);
		
		if($user instanceof User)
		{
			return $user;
		}
		
		/*TODO: utente che crea un nuovo user da provider*/
		$createdBy = "UTENTE";			
		$userID = uniqid();
		$createdAt = new \DateTime();
				
		$user = new User($userID, $createdAt, $createdBy);

		$user->setEmail($identity['email']);
		$user->setLastname($identity['lastname']);
		$user->setFirstname($identity['firstname']);

		$this->entityManager->persist($user);
		$this->entityManager->flush();
		
		return $user;
	}

	public function findOneByEmail($email)
	{		
		return $user = $this->entityManager
					        ->getRepository('Ora\User\User')
					        ->findOneBy(array('email'=> $email));
	}
}
<?php 
namespace Application\Service;

use Application\Entity\User;

interface UserService
{
	/**
	 * User Subscribe to the system the first time user log in with supported SSO
	 *
	 * @param array [email, family_name, given_name, picture]
	 * @return User
	 */	
	public function subscribeUser($userInfo);

	/**
	 * Create a User
	 *
	 * @param array [email, family_name, given_name, picture]
	 * @param string $role
	 * @return User
	 */
	public function create($infoOfUser, $role);

	/**
	 * Find a User by id
	 *
	 * @param mixed $id
	 * @return User
	 */	
	public function findUser($id);
	
	/**
	 * Find a User by Email
	 *
	 * @param string $email
	 * @return User
	 */
	public function findUserByEmail($email);
	
	/**
	 * Find a User by Kanbanize username
	 *
	 * @param string $email
	 * @return User
	 */
	public function findByKanbanizeUsername($username);
}

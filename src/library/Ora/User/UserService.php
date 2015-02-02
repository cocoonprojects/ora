<?php 

namespace Ora\User;

interface UserService
{
	/**
	 * User Subscribe to the system the first time user log in with supported SSO
	 *
	 * @param array [email, lastname, firstname]
	 */	
	public function subscribeUser($infoOfUser);
	
	/**
	 * Create a User
	 *
	 * @param array [email, lastname, firstname]	 
	 */	
	public function create($infoOfUser);
	
	/**
	 * Find a User by id
	 *
	 * @param mixed $id
	 */	
	public function findUser($id);
	
	/**
	 * Find a User by Email
	 *
	 * @param string $email
	 */
	public function findUserByEmail($email);	
	
}

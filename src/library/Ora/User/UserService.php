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
	 * @param Datetime $createdAt
	 * @param array [email, lastname, firstname]
	 * @param Role $role
	 */	
	public function create($infoOfUser, Role $role);
}
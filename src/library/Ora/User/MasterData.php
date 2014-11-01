<?php
namespace Ora\User;

use Rhumsaa\Uuid\Uuid;

interface MasterData
{
	/**
	 * @return Uuid
	 */
	public function getId();

	/**
	 * @return string
	 */
	public function getEmail();
	
	/**
	 * @return string
	 */
	public function getFirstname();
	
	/**
	 * @return string
	 */
	public function getLastname();
	
	/**
	 * @return Profile
	 */
	public function toProfile();
}
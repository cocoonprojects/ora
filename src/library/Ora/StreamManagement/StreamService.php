<?php

namespace Ora\StreamManagement;

use Ora\Organization\Organization;
use Ora\User\User;

interface StreamService
{
	/**
	 * 
	 * @param Organization $organization
	 * @param string $subject
	 * @param User $createdBy
	 * @return Stream
	 */
	public function createStream(Organization $organization, $subject, User $createdBy);
	
	public function getStream($id);
	
	public function findStream($id);
	
} 
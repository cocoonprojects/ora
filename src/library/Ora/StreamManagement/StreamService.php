<?php

namespace Ora\StreamManagement;

use Ora\Organization\Organization;
use Ora\User\User;
use Ora\ReadModel\Stream as ReadModelStream;

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
	/**
	 * 
	 * @param string|Uuid $id
	 * @return Stream|null
	 */
	public function getStream($id);
	/**
	 * 
	 * @param string $id
	 * @return ReadModelStream|null
	 */
	public function findStream($id);
	/**
	 * 
	 * @param User $user
	 * @return ReadModelStream[]
	 */
	public function findStreams(User $user);
} 
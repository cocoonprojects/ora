<?php

namespace TaskManagement\Service;

use Application\Organization;
use Application\Entity\User;
use TaskManagement\Entity\Stream as ReadModelStream;
use TaskManagement\Stream;

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
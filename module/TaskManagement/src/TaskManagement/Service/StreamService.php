<?php

namespace TaskManagement\Service;

use Application\Entity\User;
use People\Entity\Organization as ReadModelOrganization;
use People\Organization;

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
	 * @param ReadModelOrganization $organization
	 * @return ReadModelStream[]
	 */
	public function findStreams(ReadModelOrganization $organization);
} 
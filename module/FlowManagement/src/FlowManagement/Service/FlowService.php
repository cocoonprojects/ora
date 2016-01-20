<?php

namespace FlowManagement\Service;

use Application\Entity\User;
use FlowManagement\Entity\FlowCard;
use Rhumsaa\Uuid\Uuid;
use Application\Entity\BasicUser;


interface FlowService{
	
	/**
	 * Get the list of available flow cards assigned to $recipient in the $offset - $limit interval
	 * @param User $recipient
	 * @param string $offset
	 * @param string $limit
	 * @param array $filters
	 * @return FlowCard[]
	 */
	public function findFlowCards(User $recipient, $offset, $limit, $filters);

	/**
	 * Add a Lazy majority Vote Card to $recipient flow
	 * @param BasicUser $recipient
	 * @param Uuid $itemId
	 * @param Uuid $organizationid
	 * @param BasicUser $createdBy
	 */
	public function createLazyMajorityVoteCard(BasicUser $recipient, $itemId, $organizationid, BasicUser $createdBy);
}
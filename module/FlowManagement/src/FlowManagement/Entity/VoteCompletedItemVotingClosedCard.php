<?php

namespace FlowManagement\Entity;

use Doctrine\ORM\Mapping AS ORM;
use FlowManagement\FlowCardInterface;
/**
 * @ORM\Entity
 *
 */
class VoteCompletedItemVotingClosedCard extends FlowCard {
	
	public function serialize(){
		$rv = [];
		$type = FlowCardInterface::VOTE_COMPLETED_ITEM_VOTING_CLOSED_CARD;
		$content = $this->getContent();
		$rv["type"] = $type;
		$rv["createdAt"] = date_format($this->getCreatedAt(), 'c');
		$rv["id"] = $this->getId();
		$rv["title"] = "Voting closed for Completed Item";
		$rv["content"] = [
			"description" => "The vote for this work completed item is closed.",
			"actions" => [
				"primary" => [
					"text" => "Read More Here !",
					"orgId" => $content[$type]["orgId"],
					"itemId" => $this->getItem()->getId()
				],
				"secondary" => []
			],
		];
		return $rv;
	}
}
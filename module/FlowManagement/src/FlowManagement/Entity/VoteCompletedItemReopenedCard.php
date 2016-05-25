<?php

namespace FlowManagement\Entity;

use Doctrine\ORM\Mapping AS ORM;
use FlowManagement\FlowCardInterface;
/**
 * @ORM\Entity
 *
 */
class VoteCompletedItemReopenedCard extends FlowCard {
	
	public function serialize(){
		$rv = [];
		$type = FlowCardInterface::VOTE_COMPLETED_ITEM_REOPENED_CARD;
		$content = $this->getContent();
		$rv["type"] = $type;
		$rv["createdAt"] = date_format($this->getCreatedAt(), 'c');
		$rv["id"] = $this->getId();
		$rv["title"] = "Voting closed for ".$this->getItem()->getSubject()." Item";
		$rv["content"] = [
			"description" => "The vote for this work completed item is closed, and the item ".$this->getItem()->getId()." has been reopened.",
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
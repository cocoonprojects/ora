<?php

namespace FlowManagement\Entity;

use Doctrine\ORM\Mapping AS ORM;
use FlowManagement\FlowCardInterface;
/**
 * @ORM\Entity
 *
 */
class LazyMajorityVoteCard extends FlowCard {
	
	public function serialize(){
		$rv = [];
		$type = FlowCardInterface::LAZY_MAJORITY_VOTE;
		$content = $this->getContent();
		$rv["type"] = $type;
		$rv["createdAt"] = date_format($this->getCreatedAt(), 'c');
		$rv["id"] = $this->getId();
		$rv["title"] = "Lazy Majority Voting New Item Idea";
		$rv["content"] = [
			"description" => "Do you want this work item idea to be opened ?",
			"actions" => [
				"primary" => [
					"text" => "Read More Here !",
					"orgId" => $content[$type]["orgId"],
					"itemId" => $content[$type]["itemId"]
				],
				"secondary" => []
			],
		];
		return $rv;
	}
}
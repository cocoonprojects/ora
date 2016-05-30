<?php

namespace FlowManagement\Entity;

use Doctrine\ORM\Mapping AS ORM;
use FlowManagement\FlowCardInterface;
/**
 * @ORM\Entity
 *
 */
class VoteIdeaCard extends FlowCard {
	
	public function serialize(){
		$rv = [];
		$type = FlowCardInterface::VOTE_IDEA_CARD;
		$content = $this->getContent();
		$item = $this->getItem();
		$rv["type"] = $type;
		$rv["createdAt"] = date_format($this->getCreatedAt(), 'c');
		$rv["id"] = $this->getId();
		$rv["title"] = "Owner changed for '".$item->getSubject()."'";
		$rv["content"] = [
			"description" => 'The new Item owner is '.$item->getOwner()->getFirstname().' '.$item->getOwner()->getLastname(),
			"actions" => [
				"primary" => [
					"text" => "",
					"orgId" => $content[$type]["orgId"],
					"itemId" => $item->getId()
				],
				"secondary" => []
			],
		];
		return $rv;
	}
}
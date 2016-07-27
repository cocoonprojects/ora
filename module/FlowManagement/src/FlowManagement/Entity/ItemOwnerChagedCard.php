<?php

namespace FlowManagement\Entity;

use Doctrine\ORM\Mapping AS ORM;
use FlowManagement\FlowCardInterface;
/**
 * @ORM\Entity
 *
 */
class ItemOwnerChangedCard extends FlowCard {
	
	public function serialize(){
		$rv = [];
		$type = FlowCardInterface::ITEM_OWNER_CHANGED_CARD;
		$content = $this->getContent();
		$item = $this->getItem();
		$owner = $item->getOwner()->getMember();
		$rv["type"] = $type;
		$rv["createdAt"] = date_format($this->getCreatedAt(), 'c');
		$rv["id"] = $this->getId();
		$rv["title"] = "Owner changed for '".$item->getSubject()."'";
		$rv["content"] = [
			"description" => 'The new Item owner is '.$owner->getFirstname().' '.$owner->getLastname(),
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
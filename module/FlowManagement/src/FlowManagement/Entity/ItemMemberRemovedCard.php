<?php

namespace FlowManagement\Entity;

use Doctrine\ORM\Mapping AS ORM;
use FlowManagement\FlowCardInterface;

/**
 * @ORM\Entity
 */
class ItemMemberRemovedCard extends FlowCard {

	public function serialize(){
		$rv = [];
				$rv = [];
		$type = FlowCardInterface::ITEM_MEMBER_REMOVED_CARD;
		$content = $this->getContent();
		$item = $this->getItem();
		$owner = $item->getOwner()->getMember();
		$rv["type"] = $type;
		$rv["createdAt"] = date_format($this->getCreatedAt(), 'c');
		$rv["id"] = $this->getId();
		$rv["title"] = "Member removed from '".$item->getSubject()."'";
		$rv["content"] = [
			"description" => 'The user '.$content[$type]['userName'].' is no more a member of this item',
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
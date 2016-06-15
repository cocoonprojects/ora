<?php

namespace FlowManagement\Entity;

use Doctrine\ORM\Mapping AS ORM;
use FlowManagement\FlowCardInterface;

/**
 * @ORM\Entity
 */
class OrganizationMemberRoleChangedCard extends FlowCard {

	public function serialize(){
		$rv = [];

		$type = FlowCardInterface::ORGANIZATION_MEMBER_ROLE_CHANGED_CARD;

		$content = $this->getContent();

		$title = "User {$content[$type]['userName']} role changed";
		$description = sprintf(
			"User %s new role is %s (was %s)",
			$content[$type]['userName'],
			$content[$type]['newRole'],
			$content[$type]['oldRole']
		);

		if (isset($content[$type]['by'])) {
			$description .= sprintf(
				". Change performed by %s", $content[$type]['by']
			);
		}

		$rv["type"] = $type;
		$rv["createdAt"] = date_format($this->getCreatedAt(), 'c');
		$rv["id"] = $this->getId();
		$rv["title"] = $title;
		$rv["content"] = [
			"description" => $description,
			"actions" => [
				"primary" => [
					"text" => "",
					"orgId" => $content[$type]["orgId"]
				],
				"secondary" => []
			],
		];
		return $rv;
	}
}
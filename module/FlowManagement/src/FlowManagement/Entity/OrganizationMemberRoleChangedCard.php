<?php

namespace FlowManagement\Entity;

use Doctrine\ORM\Mapping AS ORM;
use FlowManagement\FlowCardInterface;
/**
 * @ORM\Entity
 *
 */
class OrganizationMemberRoleChangedCard extends FlowCard {
	
	public function serialize(){
		$rv = [];
		$type = FlowCardInterface::ORGANIZATION_MEMBER_ROLE_CHANGED_CARD;
		$content = $this->getContent();
		$rv["type"] = $type;
		$rv["createdAt"] = date_format($this->getCreatedAt(), 'c');
		$rv["id"] = $this->getId();
		$rv["title"] = "User '".$content[$type]['userName']."' changed role from '".$content[$type]['oldRole']."'' to '".$content[$type]['newRole']."'";
		$rv["content"] = [
			"description" => '',
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
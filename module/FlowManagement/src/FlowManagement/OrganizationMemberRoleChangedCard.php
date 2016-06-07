<?php

namespace FlowManagement;

use Rhumsaa\Uuid\Uuid;
use Application\Entity\BasicUser;

class OrganizationMemberRoleChangedCard extends FlowCard{
	public static function create(BasicUser $recipient, $content, BasicUser $by, $item = null){
		$rv = new self();
		$event = FlowCardCreated::occur(Uuid::uuid4()->toString(), [
				'to' => $recipient->getId(),
				'content' => $content,
				'by' => $by->getId()
		]);
		$rv->recordThat($event);
		return $rv;
	}
	
	protected function whenFlowCardCreated(FlowCardCreated $event){
		parent::whenFlowCardCreated($event);
		$this->content = [FlowCardInterface::ORGANIZATION_MEMBER_ROLE_CHANGED_CARD => $event->payload()['content']];
	}
}
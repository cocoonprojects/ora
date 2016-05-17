<?php

namespace FlowManagement;

use Rhumsaa\Uuid\Uuid;
use Application\Entity\BasicUser;

class VoteCompletedItemReopenedCard extends FlowCard {
	public static function create(BasicUser $recipient, $content, BasicUser $by, $itemId = null){
		$rv = new self();
		$event = FlowCardCreated::occur(Uuid::uuid4()->toString(), [
				'to' => $recipient->getId(),
				'content' => $content,
				'by' => $by->getId(),
				'item' => $itemId
		]);
		$rv->recordThat($event);
		return $rv;
	}
	
	protected function whenFlowCardCreated(FlowCardCreated $event) {
		parent::whenFlowCardCreated($event);
		$this->content = [FlowCardInterface::VOTE_COMPLETED_ITEM_REOPENED_CARD => $event->payload()['content']];
		$this->itemId = $event->payload()['item'];
	}
}
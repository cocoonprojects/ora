<?php

namespace FlowManagement;

use Application\DomainEntity;
use Rhumsaa\Uuid\Uuid;
use Application\Entity\BasicUser;

class FlowCard extends DomainEntity implements FlowCardInterface {

	/**
	 * @var BasicUser
	 */
	private $recipient;
	/**
	 * @var array
	 */
	private $contents;
	/**
	 * @var \DateTime
	*/
	protected $createdAt;
	/**
	 * @var BasicUser
	 */
	protected $createdBy;
	/**
	 * @var \DateTime
	 */
	protected $mostRecentEditAt;
	/**
	 * @var BasicUser
	 */
	protected $mostRecentEditBy;

	public static function create(BasicUser $recipient, $content, BasicUser $by){
		$rv = new self();
		$event = FlowCardCreated::occur(Uuid::uuid4()->toString(), [
				'to' => $recipient->getId(),
				'content' => $content,
				'by' => $by->getId()
		]);
		$rv->recordThat($event);
		return $rv;
	}

	protected function whenFlowCardCreated(FlowCardCreated $event) {
		$this->id = Uuid::fromString($event->aggregateId());
		$this->recipient = BasicUser::createBasicUser($event->payload()['to']);
		$this->contents = [FlowCardInterface::LAZY_MAJORITY_VOTE => $event->payload()['content']];
		$this->createdAt = $this->mostRecentEditAt = $event->occurredOn();
		$this->createdBy = $this->mostRecentEditBy = BasicUser::createBasicUser($event->payload()['by']);
	}
	
	public function getRecipient(){
		return $this->recipient;
	}
	
	public function getMostRecentEditAt(){
		return $this->mostRecentEditAt;
	}
	
	public function getMostRecentEditBy(){
		return $this->mostRecentEditBy;
	}
	
	public function getContents(){
		return $this->contents;
	}
}
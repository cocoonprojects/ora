<?php

namespace TaskManagement;

use Rhumsaa\Uuid\Uuid;
use Application\DomainEntity;
use Application\Entity\User;
use People\Organization;

/**
 * 
 */
class Stream extends DomainEntity
{	    
	/**
	 * @var string
	 */
	private $subject;
	/**
	 * @var Uuid
	 */
	private $organizationId;
	/**
	 * @var \DateTime
	 */
	private $createdAt;

	public static function create(Organization $organization, $subject, User $createdBy)
	{
		$rv = new self();
		$rv->recordThat(StreamCreated::occur(Uuid::uuid4()->toString(), [
			'organizationId' => $organization->getId(),
			'by' => $createdBy->getId(),
		]));
		$rv->setSubject($subject, $createdBy);
		return $rv;
	}
	
	public function getSubject() {
		return $this->subject;
	}
	
	public function setSubject($subject, User $createdBy) {
		$this->recordThat(StreamUpdated::occur($this->id->toString(), [
			'subject' => is_null($subject) ? null : trim($subject),
			'by' => $createdBy->getId()
		]));
		return $this;
	}

	public function changeOrganization(Organization $organization, User $by) {
		$this->recordThat(StreamOrganizationChanged::occur($this->id->toString(), [
			'organizationId' => $organization->getId(),
			'by' => $by->getId()
		]));
	}
	
	public function getOrganizationId() {
		return $this->organizationId->toString();
	}

	/**
	 * @return \DateTime
	 */
	public function getCreatedAt()
	{
		return $this->createdAt;
	}

	protected function whenStreamCreated(StreamCreated $event) {
		$this->id = Uuid::fromString($event->aggregateId());
		$this->organizationId = Uuid::fromString($event->payload()['organizationId']);
		$this->createdAt = $event->occurredOn();
	}
	
	protected function whenStreamUpdated(StreamUpdated $event) {
		if(isset($event->payload()['subject'])) {
			$this->subject = $event->payload()['subject'];
		}
	}
	
	protected function whenStreamOrganizationChanged(StreamOrganizationChanged $event) {
		$this->organizationId = Uuid::fromString($event->payload()['organizationId']);
	}
}
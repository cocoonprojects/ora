<?php

namespace Ora\StreamManagement;

use Rhumsaa\Uuid\Uuid;
use Ora\DomainEntity;
use Ora\User\User;
use Ora\Organization\Organization;

/**
 * 
 */
class Stream extends DomainEntity
{	    
	/**
	 * 
	 * @var string
	 */
	private $subject;
	
	/**
	 *
	 * @var Uuid
	 */
	private $organizationId;	
	
	public static function create(Organization $organization, $subject, User $createdBy) 
	{
		$rv = new self();
		$rv->recordThat(StreamCreated::occur(Uuid::uuid4()->toString(), [
			'organizationId' => $organization->getId()->toString(),
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
		$this->recordThat(OrganizationChanged::occur($this->id->toString(), [
			'organizationId' => $organization->getId()->toString(),
			'by' => $by->getId()
		]));
	}
	
	public function getOrganizationId() {
		return $this->organizationId;
	}
	
	protected function whenStreamCreated(StreamCreated $event) {
		$this->id = Uuid::fromString($event->aggregateId());
		$this->organizationId = Uuid::fromString($event->payload()['organizationId']);
	}
	
	protected function whenStreamUpdated(StreamUpdated $event) {
		if(isset($event->payload()['subject'])) {
			$this->subject = $event->payload()['subject'];
		}
	}
	
	protected function whenOrganizationChanged(OrganizationChanged $event) {
		$this->organizationId = Uuid::fromString($event->payload()['organizationId']);
	}
}
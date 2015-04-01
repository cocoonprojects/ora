<?php

namespace Application;

use Rhumsaa\Uuid\Uuid;
use Ora\DomainEntity;
use Ora\User\User;
use Ora\Accounting\Account;
use Ora\DuplicatedDomainEntityException;
use Ora\DomainEntityUnavailableException;

class Organization extends DomainEntity
{
	CONST ROLE_MEMBER = 'member';
	CONST ROLE_ADMIN  = 'admin';
	
	CONST EVENT_CREATED = 'Organization.Created';
	/**
	 * 
	 * @var string
	 */
	private $name;
	/**
	 * 
	 * @var Uuid
	 */
	private $accountId;
	/**
	 * 
	 * @var array
	 */
	private $members = array();
		
	public static function create($name, User $createdBy) {
		$rv = new self();
		$rv->recordThat(OrganizationCreated::occur(Uuid::uuid4()->toString(), array(
				'by' => $createdBy->getId(),
		)));
		$rv->setName($name, $createdBy);		
		$rv->addMember($createdBy, $createdBy, self::ROLE_ADMIN);
		return $rv;
	}
	
	public function setName($name, User $updatedBy) {
		$s = is_null($name) ? null : trim($name);
		$this->recordThat(OrganizationUpdated::occur($this->id->toString(), array(
			'name' => $s,
			'by' => $updatedBy->getId(),
		)));
		return $this;
	}
	
	public function getName() {
		return $this->name;
	}
	
	public function changeAccount(Account $account, User $updatedBy) {
		$payload = array(
				'accountId' => $account->getId()->toString(),
				'by' => $updatedBy->getId(),
		);
		if(!is_null($this->accountId)) {
			$payload['prevAccountId'] = $this->accountId->toString();
		}
		$this->recordThat(OrganizationAccountChanged::occur($this->id->toString(), $payload));
		return $this;
	}
	
	public function getAccountId() {
		return $this->accountId;
	}
	
	public function addMember(User $user, User $addedBy, $role = self::ROLE_MEMBER) {
		if (array_key_exists($user->getId(), $this->members)) {
			throw new DuplicatedDomainEntityException($this, $user);
		}
		$this->recordThat(OrganizationMemberAdded::occur($this->id->toString(), array(
			'userId' => $user->getId(),
			'role' => $role,
			'by' => $addedBy->getId(),
		)));
	}

	public function removeMember(User $member, User $removedBy)
	{
		if (!array_key_exists($member->getId(), $this->members)) {
			throw new DomainEntityUnavailableException($this, $member); 
		}
		$this->recordThat(OrganizationMemberRemoved::occur($this->id->toString(), array(
			'userId' => $member->getId(),
			'by' => $removedBy->getId(),
		)));
	}
	
	public function getMembers() {
		return $this->members;
	}
	
	protected function whenOrganizationCreated(OrganizationCreated $event)
	{
		$this->id = Uuid::fromString($event->aggregateId());
	}
	
	protected function whenOrganizationUpdated(OrganizationUpdated $event) {
		$pl = $event->payload();
		if(array_key_exists('name', $pl)) {
			$this->name = $pl['name'];
		}
	}
	
	protected function whenOrganizationAccountChanged(OrganizationAccountChanged $event) {
		$p = $event->payload();
		$this->accountId = Uuid::fromString($p['accountId']);
	}
	
	protected function whenOrganizationMemberAdded(OrganizationMemberAdded $event) {
		$p = $event->payload();
		$id = $p['userId'];
		$this->members[$id]['role'] = $p['role'];
	}

	protected function whenOrganizationMemberRemoved(OrganizationMemberRemoved $event) {
		$p = $event->payload();
		$id = $p['userId'];
		unset($this->members[$id]);
	}
	
}
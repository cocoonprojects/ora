<?php

namespace People;

use People\Entity\OrganizationMembership;
use Rhumsaa\Uuid\Uuid;
use Application\Entity\User;
use Application\DomainEntity;
use Application\DuplicatedDomainEntityException;
use Application\DomainEntityUnavailableException;
use Application\InvalidArgumentException;
use Accounting\Account;

class Organization extends DomainEntity
{
	CONST ROLE_MEMBER = 'member';
	CONST ROLE_ADMIN  = 'admin';
	CONST SETTING_KANBANIZE_SUBDOMAIN = "kanbanizeSubdomain";
	/**
	 * @var string
	 */
	private $name;
	/**
	 * @var Uuid
	 */
	private $accountId;
	/**
	 * @var array
	 */
	private $members = [];
	/**
	 * @var \DateTime
	 */
	private $createdAt;
	/**
	 *
	 * @var array
	 */
	private $settings;
		
	public static function create($name, User $createdBy) {
		$rv = new self();
		$rv->recordThat(OrganizationCreated::occur(Uuid::uuid4()->toString(), array(
			'by' => $createdBy->getId(),
		)));
		$rv->setName($name, $createdBy);
		$rv->addMember($createdBy, self::ROLE_ADMIN);
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
	
	public function setSetting($key, $value, User $updatedBy){
		if(is_null($key)){
			throw new InvalidArgumentException('Cannot address setting without key');
		}
		$this->recordThat(OrganizationUpdated::occur($this->id->toString(), array(
			'key' => trim($key),
			'value' => trim($value),
			'by' => $updatedBy->getId(),
		)));
		return $this;
	}

	public function getSettings(){
		return $this->settings;
	}

	public function getSetting($key){
		return $this->settings[$key];
	}

	public function getName() {
		return $this->name;
	}
	
	public function changeAccount(Account $account, User $updatedBy) {
		$payload = array(
				'accountId' => $account->getId(),
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
	
	public function addMember(User $user, $role = self::ROLE_MEMBER, User $addedBy = null) {
		if (array_key_exists($user->getId(), $this->members)) {
			throw new DuplicatedDomainEntityException($this, $user);
		}
		$this->recordThat(OrganizationMemberAdded::occur($this->id->toString(), array(
			'userId' => $user->getId(),
			'role' => $role,
			'by' => $addedBy == null ? $user->getId() : $addedBy->getId(),
		)));
	}

	public function removeMember(User $member, User $removedBy = null)
	{
		if (!array_key_exists($member->getId(), $this->members)) {
			throw new DomainEntityUnavailableException($this, $member); 
		}
		$this->recordThat(OrganizationMemberRemoved::occur($this->id->toString(), array(
			'userId' => $member->getId(),
			'by' => $removedBy == null ? $member->getId() : $removedBy->getId(),
		)));
	}

	/**
	 * @return \DateTime
	 */
	public function getCreatedAt()
	{
		return $this->createdAt;
	}

	/**
	 * @return array
	 */
	public function getMembers() {
		return $this->members;
	}

	/**
	 * @return array
	 */
	public function getAdmins() {
		return array_filter($this->members, function($profile) {
			return $profile['role'] == self::ROLE_ADMIN;
		});
	}
	
	protected function whenOrganizationCreated(OrganizationCreated $event)
	{
		$this->id = Uuid::fromString($event->aggregateId());
		$this->createdAt = $event->occurredOn();
	}

	protected function whenOrganizationUpdated(OrganizationUpdated $event) {
		$pl = $event->payload();
		if(array_key_exists('name', $pl)) {
			$this->name = $pl['name'];
		}
		if(array_key_exists('key', $pl) && array_key_exists('value', $pl)) {
			$this->settings[$pl['key']] = $pl['value'];
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
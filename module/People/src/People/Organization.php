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
	CONST ROLE_CONTRIBUTOR  = 'contributor';
	CONST KANBANIZE_SETTINGS = 'kanbanize';
	CONST MIN_KANBANIZE_COLUMN_NUMBER = 6; // based on the count of TaskInterface STATUSes

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
	private $settings = [];
		
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
	
	public function setSettings($settingKey, $settingValue, User $updatedBy){
		if(is_null($settingKey)){
			throw new InvalidArgumentException('Cannot address setting without a setting key');
		}
		$this->recordThat(OrganizationUpdated::occur($this->id->toString(), array(
			'settingKey' => trim($settingKey),
			'settingValue' => $settingValue,
			'by' => $updatedBy->getId(),
		)));
		return $this;
	}

	public function getSettings($key = null){
		if(is_null($key)){
			return $this->settings;
		}
		if(array_key_exists($key, $this->settings)){
			return $this->settings[$key];
		}
		return null;
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
	
	public function addMember(User $user, $role = self::ROLE_CONTRIBUTOR, User $addedBy = null) {
		if (array_key_exists($user->getId(), $this->members)) {
			throw new DuplicatedDomainEntityException($this, $user);
		}
		$this->recordThat(OrganizationMemberAdded::occur($this->id->toString(), array(
			'userId' => $user->getId(),
			'role' => $role,
			'by' => $addedBy == null ? $user->getId() : $addedBy->getId(),
		)));
	}

	public function changeMemberRole(User $member, $role, User $changedBy = null) {
		if (!array_key_exists($member->getId(), $this->members)) {
			throw new DomainEntityUnavailableException($this, $member); 
		}

		$this->recordThat(OrganizationMemberRoleChanged::occur($this->id->toString(), array(
			'userId' => $member->getId(),
			'organizationId' => $this->getId(),
			'newRole' => $role,
			'oldRole' => $this->members[$member->getId()]['role'],
			'by' => $changedBy == null ? $member->getId() : $changedBy->getId(),
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
		if(array_key_exists('settingKey', $pl) && array_key_exists('settingValue', $pl)) {
			if(is_array($pl['settingValue'])){
				foreach ($pl['settingValue'] as $key=>$value){
					$this->settings[$pl['settingKey']][$key] = $value;
				}
			}else{
				$this->settings[$pl['settingKey']] = $pl['settingValue'];
			}
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
	
	protected function whenOrganizationMemberRoleChanged(OrganizationMemberRoleChanged $event) {
		$p = $event->payload();
		$id = $p['userId'];
		$this->members[$id]['role'] = $p['newRole'];
	}

	protected function whenOrganizationMemberRemoved(OrganizationMemberRemoved $event) {
		$p = $event->payload();
		$id = $p['userId'];
		unset($this->members[$id]);
	}
}
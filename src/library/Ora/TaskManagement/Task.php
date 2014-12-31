<?php

namespace Ora\TaskManagement;

use Ora\IllegalStateException;
use Ora\DuplicatedDomainEntityException;
use Ora\DomainEntityUnavailableException;
use Ora\StreamManagement\Stream;
use Ora\User\User;
use Ora\DomainEntity;
use Rhumsaa\Uuid\Uuid;
use Ora\ReadModel\Estimation;
use Ora\ReadModel\TaskMember;

/**
 * 
 * @author Giannotti Fabio
 *
 */
class Task extends DomainEntity implements \Serializable
{	
    CONST STATUS_IDEA = 0;
    CONST STATUS_OPEN = 10;
    CONST STATUS_ONGOING = 20;
    CONST STATUS_COMPLETED = 30;
    CONST STATUS_ACCEPTED = 40;
    CONST STATUS_DELETED = -10;
    
    CONST ROLE_MEMBER = 'member';
    CONST ROLE_OWNER  = 'owner';
    
    /**
	 * 
	 * @var string
	 */
	private $subject;
	
	/**
	 * 
	 * @var int
	 */
	private $status;
	
	/**
	 * 
	 * @var Uuid
	 */
	private $streamId;
	
	/**
	 */
	private $members = array();

	/**
	 */
	private $estimations = array();
	
	public static function create(Stream $stream, $subject, User $createdBy) {
		$rv = new self();
		$rv->id = Uuid::uuid4();
		$rv->status = self::STATUS_ONGOING;
		$rv->recordThat(TaskCreated::occur($rv->id->toString(), array(
				'status' => $rv->status,
				'by' => $createdBy->getId(),
		)));
		$rv->setSubject($subject, $createdBy);
		$rv->changeStream($stream, $createdBy);
		$rv->addMember($createdBy, $createdBy, self::ROLE_OWNER);
		return $rv;
	}
	
	public function delete(User $deletedBy) {
		if($this->getStatus() == Task::STATUS_COMPLETED || $this->getStatus() == Task::STATUS_ACCEPTED) {
			throw new IllegalStateException('Cannot delete a task in state '.$this->getStatus().'. Task '.$this->getId()->toString().' won\'t be deleted');
		}
		$this->recordThat(TaskDeleted::occur($this->id->toString(), array(
			'prevStatus' => $this->getStatus(),
			'by'  => $deletedBy->getId(),
		)));
	}
	
	public function getStatus() {
	    return $this->status;
	}
	
	public function complete(User $completedBy) {
		$this->recordThat(TaskCompleted::occur($this->id->toString(), array(
			'prevStatus' => $this->getStatus(),
			'by' => $completedBy->getId(),
		)));
	}
	
	public function accept(User $acceptedBy) {
		$this->recordThat(TaskAccepted::occur($this->id->toString(), array(
			'prevStatus' => $this->getStatus(),
			'by' => $acceptedBy->getId(),
		)));
	}
	
	public function getSubject() {
		return $this->subject;
	}
	
	public function setSubject($subject, User $updatedBy) {
		$this->recordThat(TaskUpdated::occur($this->id->toString(), array(
			'subject' => $subject,
			'by' => $updatedBy->getId(),
		)));
	}
	
	public function changeStream(Stream $stream, User $updatedBy) {
		if($this->status == self::STATUS_COMPLETED || $this->status == self::STATUS_ACCEPTED) {
			throw new IllegalStateException('Cannot set the task stream in '.$this->status.' state');
		}
		$payload = array(
				'streamId' => $stream->getId()->toString(),
				'by' => $updatedBy->getId(),
		);
		if(!is_null($this->streamId)) {
			$payload['prevStreamId'] = $this->streamId->toString();
		}
		$this->recordThat(StreamChanged::occur($this->id->toString(), $payload));
	}
		
	public function getStreamId() {
	    return $this->streamId;
	}
	
	public function addMember(User $user, User $addedBy, $role = self::ROLE_MEMBER)
	{
		if($this->status == self::STATUS_COMPLETED || $this->status == self::STATUS_ACCEPTED) {
			throw new IllegalStateException('Cannot add a member to a task in '.$this->status.' state');
		}
        if (array_key_exists($user->getId(), $this->members)) {
        	throw new DuplicatedDomainEntityException($this, $user); 
        }
        $this->recordThat(MemberAdded::occur($this->id->toString(), array(
        	'userId' => $user->getId(),
        	'role' => $role,
        	'by' => $addedBy->getId(),
        )));
	}
	
	public function removeMember(User $member, User $removedBy)
	{
		if($this->status == self::STATUS_COMPLETED || $this->status == self::STATUS_ACCEPTED) {
			throw new IllegalStateException('Cannot remove a member from a task in '.$this->status.' state');
		}
		// TODO: Integrare controllo per cui è possibile effettuare l'UNJOIN
        // solo nel caso in cui non sia stata ancora effettuata nessuna stima
		if (!array_key_exists($member->getId(), $this->members)) {
        	throw new DomainEntityUnavailableException($this, $member); 
        }
		$this->recordThat(MemberRemoved::occur($this->id->toString(), array(
			'userId' => $member->getId(),
        	'by' => $removedBy->getId(),
        )));
	}
	
	public function addEstimation($value, User $member) {
		if($this->status != self::STATUS_ONGOING) {
			throw new IllegalStateException('Cannot estimate a task in the state '.$this->status.'.');
		}
		//check if the estimator is a task member
		if(!array_key_exists($member->getId(), $this->members)) {
        	throw new DomainEntityUnavailableException($this, $member); 
		}
		//this estimation already exists?
        if (array_key_exists($member->getId(), $this->estimations)) {
        	throw new DuplicatedDomainEntityException($this, $member); 
        }
		//record the estimation
		$estId = Uuid::uuid4();
        $this->recordThat(EstimationAdded::occur($this->id->toString(), array(
        	'id'	 => $estId,
        	'by' => $member->getId(),
        	'value'  => $value,
        )));
	}
	
	public function getMembers() {
	    return $this->members;
	}
	
	public function getEstimations() {
		return $this->estimations;
	}
	
	public function serialize()
	{
		$data = array(
			'id' => $this->id->toString(),
			'subject' => $this->subject,
			'status' => $this->status,
		);
	    return serialize($data); 
	}
	
	public function unserialize($encodedData)
	{
	    $data = unserialize($encodedData);
	    $this->id = Uuid::fromString($data['id']);
	    $this->subject = $data['subject'];
	    $this->status = $data['status'];
	}
		
	protected function whenTaskCreated(TaskCreated $event)
	{
		$this->id = Uuid::fromString($event->aggregateId());
		$this->status = $event->payload()['status'];
	}
	
	protected function whenTaskCompleted(TaskCompleted $event) {
		$this->status = self::STATUS_COMPLETED;
	}
	
	protected function whenTaskAccepted(TaskAccepted $event) {
		$this->status = self::STATUS_ACCEPTED;
	}
	
	protected function whenTaskDeleted(TaskDeleted $event) {
		$this->status = self::STATUS_DELETED;
	}
	
	protected function whenTaskUpdated(TaskUpdated $event) {
		if(isset($event->payload()['subject'])) {
			$this->subject = $event->payload()['subject'];
		}
	}
	
	protected function whenMemberAdded(MemberAdded $event) {
		$p = $event->payload();
		$id = $p['userId'];
		$this->members[$id] = $p['role'];
	}

	protected function whenMemberRemoved(MemberRemoved $event) {
		$p = $event->payload();
		$id = $p['userId'];
		unset($this->members[$id]);
	}
	
	protected function whenStreamChanged(StreamChanged $event) {
		$p = $event->payload();
		$this->streamId = Uuid::fromString($p['streamId']);
		
	}
	
	protected function whenEstimationAdded(EstimationAdded $event) {
		$p = $event->payload();
		$id = $p['by'];
		$this->estimations[$id] = $p['value'];
	}
	
}
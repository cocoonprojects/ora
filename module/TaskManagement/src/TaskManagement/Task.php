<?php
namespace TaskManagement;

use Rhumsaa\Uuid\Uuid;
use Application\IllegalStateException;
use Application\InvalidArgumentException;
use Application\DuplicatedDomainEntityException;
use Application\DomainEntityUnavailableException;
use Application\DomainEntity;
use Application\Entity\User;

class Task extends DomainEntity
{	
	CONST STATUS_DELETED   = -10;
	CONST STATUS_IDEA      = 0;
	CONST STATUS_OPEN      = 10;
	CONST STATUS_ONGOING   = 20;
	CONST STATUS_COMPLETED = 30;
	CONST STATUS_ACCEPTED  = 40;
	CONST STATUS_CLOSED    = 50;

	CONST ROLE_MEMBER = 'member';
	CONST ROLE_OWNER  = 'owner';
	
	CONST NOT_ESTIMATED = -1;
	
	CONST EVENT_ONGOING          = 'Task.Ongoing';
	CONST EVENT_COMPLETED        = 'Task.Completed';
	CONST EVENT_ACCEPTED         = 'Task.Accepted';
	CONST EVENT_CLOSED           = 'Task.Closed';
	CONST EVENT_SHARES_ASSIGNED  = 'Task.SharesAssigned';
	CONST EVENT_ESTIMATION_ADDED = 'Task.EstimationAdded';
	
	
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
	
	public static function create(Stream $stream, $subject, User $createdBy, array $options = null) {
		$rv = new self();
		$rv->recordThat(TaskCreated::occur(Uuid::uuid4()->toString(), array(
				'status' => self::STATUS_ONGOING,
				'by' => $createdBy->getId(),
		)));
		$rv->setSubject($subject, $createdBy);
		$rv->changeStream($stream, $createdBy);
		return $rv;
	}
	
	public function delete(User $deletedBy) {
		if($this->getStatus() >= self::STATUS_COMPLETED) {
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
	
	public function execute(User $executedBy) {
		if(!in_array($this->status, [self::STATUS_OPEN, self::STATUS_COMPLETED])) {
			throw new IllegalStateException('Cannot execute a task in '.$this->status.' state');
		}
		if(!isset($this->members[$executedBy->getId()]) || $this->members[$executedBy->getId()]['role'] != self::ROLE_OWNER) {
			throw new InvalidArgumentException('Only the owner can put in execution the task');
		}
		$this->recordThat(TaskOngoing::occur($this->id->toString(), array(
				'prevStatus' => $this->getStatus(),
				'by' => $executedBy->getId(),
		)));
		$this->getEventManager()->trigger(self::EVENT_ONGOING, $this, ['by' => $executedBy]);
		return $this;
	}
	
	public function complete(User $completedBy) {
		if(!in_array($this->status, [self::STATUS_ONGOING, self::STATUS_ACCEPTED])) {
			throw new IllegalStateException('Cannot complete a task in '.$this->status.' state');
		}
		if(!isset($this->members[$completedBy->getId()]) || $this->members[$completedBy->getId()]['role'] != self::ROLE_OWNER) {
			throw new InvalidArgumentException('Only the owner can mark as completed the task');
		}
		$this->recordThat(TaskCompleted::occur($this->id->toString(), array(
			'prevStatus' => $this->getStatus(),
			'by' => $completedBy->getId(),
		)));
		$this->getEventManager()->trigger(self::EVENT_COMPLETED, $this, ['by' => $completedBy]);
		return $this;
	}
	
	public function accept(User $acceptedBy) {
		if($this->status != self::STATUS_COMPLETED) {
			throw new IllegalStateException('Cannot accept a task in '.$this->status.' state');
		}
		if(is_null($this->getAverageEstimation())) {
			throw new IllegalStateException('Cannot accept a task with missing estimations by members');
		}
		if(!isset($this->members[$acceptedBy->getId()]) || $this->members[$acceptedBy->getId()]['role'] != self::ROLE_OWNER) {
			throw new InvalidArgumentException('Only the owner can accept the task');
		}
		$this->recordThat(TaskAccepted::occur($this->id->toString(), array(
			'prevStatus' => $this->getStatus(),
			'by' => $acceptedBy->getId(),
		)));
		$this->getEventManager()->trigger(self::EVENT_ACCEPTED, $this, ['by' => $acceptedBy]);
		return $this;
	}
	
	public function close(User $closedBy) {
		if($this->status != self::STATUS_ACCEPTED) {
			throw new IllegalStateException('Cannot close a task in '.$this->status.' state');
		}
		if(!isset($this->members[$closedBy->getId()])) {
			// Closing isn't restricted to the owner because it is triggered by an event (last share assignement)
			throw new InvalidArgumentException('Only a member can close the task');
		}
		$this->recordThat(TaskClosed::occur($this->id->toString(), array(
				'by' => $closedBy->getId(),
		)));
		
		$this->getEventManager()->trigger(Task::EVENT_CLOSED, $this, ['by' => $closedBy]);
		
		return $this;
	}
	
	public function getSubject() {
		return $this->subject;
	}
	
	public function setSubject($subject, User $updatedBy) {
		$s = is_null($subject) ? null : trim($subject);
		$this->recordThat(TaskUpdated::occur($this->id->toString(), array(
			'subject' => $s,
			'by' => $updatedBy->getId(),
		)));
		return $this;
	}
	
	public function changeStream(Stream $stream, User $updatedBy) {
		if($this->status >= self::STATUS_COMPLETED) {
			throw new IllegalStateException('Cannot set the task stream in '.$this->status.' state');
		}
		$payload = array(
				'streamId' => $stream->getId()->toString(),
				'by' => $updatedBy->getId(),
		);
		if(!is_null($this->streamId)) {
			$payload['prevStreamId'] = $this->streamId->toString();
		}
		$this->recordThat(TaskStreamChanged::occur($this->id->toString(), $payload));
		return $this;
	}
		
	public function getStreamId() {
		return $this->streamId;
	}
	
	/**
	 * 
	 * @param User $user
	 * @param string $role
	 * @param string $accountId
	 * @param User $addedBy
	 * @throws IllegalStateException
	 * @throws DuplicatedDomainEntityException
	 */
	public function addMember(User $user, $role = self::ROLE_MEMBER, $accountId = null, User $addedBy = null)
	{ 
		if($this->status >= self::STATUS_COMPLETED) {
			throw new IllegalStateException('Cannot add a member to a task in '.$this->status.' state');
		}
		if (array_key_exists($user->getId(), $this->members)) {
			   throw new DuplicatedDomainEntityException($this, $user); 
		}
		
		$by = is_null($addedBy) ? $user : $addedBy;
		
		$this->recordThat(TaskMemberAdded::occur($this->id->toString(), array(
			'userId' => $user->getId(),
			'accountId' => $accountId,
			'role' => $role,
			'email' => $user->getEmail(),
			'by' => $by->getId(),
		)));
		return $this;
	}
	
	public function removeMember(User $member, User $removedBy = null)
	{
		if($this->status >= self::STATUS_COMPLETED) {
			throw new IllegalStateException('Cannot remove a member from a task in '.$this->status.' state');
		}
		// TODO: Integrare controllo per cui è possibile effettuare l'UNJOIN
		// solo nel caso in cui non sia stata ancora effettuata nessuna stima
		if (!array_key_exists($member->getId(), $this->members)) {
			throw new DomainEntityUnavailableException($this, $member); 
		}

		$by = is_null($removedBy) ? $member : $removedBy;
		
		$this->recordThat(TaskMemberRemoved::occur($this->id->toString(), array(
			'userId' => $member->getId(),
			'by' => $by->getId(),
		)));
	}
	
	public function addEstimation($value, User $member) {
		if(!in_array($this->status, [self::STATUS_ONGOING, self::STATUS_COMPLETED])) {
			throw new IllegalStateException('Cannot estimate a task in the state '.$this->status.'.');
		}
		//check if the estimator is a task member
		if(!array_key_exists($member->getId(), $this->members)) {
			throw new DomainEntityUnavailableException($this, $member); 
		}
		// TODO: Estimation need an id?
		$this->recordThat(EstimationAdded::occur($this->id->toString(), array(
			'by' => $member->getId(),
			'value'	 => $value,
		)));
		
		$this->getEventManager()->trigger(self::EVENT_ESTIMATION_ADDED, $this, ['by' => $member]);
	}
	/**
	 * 
	 * @param array $shares Map of memberId and its share for each member
	 * @param User $member
	 * @throws IllegalStateException
	 * @throws DomainEntityUnavailableException
	 * @throws InvalidArgumentException
	 */
	public function assignShares(array $shares, User $member) {
		if($this->status != self::STATUS_ACCEPTED) {
			throw new IllegalStateException('Cannot assign shares in a task in status '.$this->status);
		}
		//check if the evaluator is a task member
		if(!array_key_exists($member->getId(), $this->members)) {
			throw new DomainEntityUnavailableException($this, $member); 
		}
		
		$membersShares = array();
		foreach($shares as $key => $value) {
			if(array_key_exists($key, $this->members)) {
				$membersShares[$key] = $value; 
			}
		}

		/** With PHP 5.6 the previous chunk of code can be replaced with the following
		$membersShares = array_filter($shares, function($key, $value) {
			return array_key_exists($key, $this->members);
		}, ARRAY_FILTER_USE_BOTH);
		*/

		if(array_sum($membersShares) != 1) {
			throw new InvalidArgumentException('The total amout of shares must be 100. Check the sum of assigned shares');
		}

		$missing = array_diff_key($this->members, $membersShares);
		if(count($missing) == 1) {
			throw new InvalidArgumentException('1 task member has missing share. Check the value for member ' . implode(',', array_keys($missing)));
		} elseif (count($missing) > 1) {
			throw new InvalidArgumentException(count($missing) . ' task members have missing shares. Check values for ' . implode(',', array_keys($missing)) . ' members');
		}

		$this->recordThat(SharesAssigned::occur($this->id->toString(), array(
			'shares' => $membersShares,
			'by' => $member->getId(),
		)));	

		$this->getEventManager()->trigger(self::EVENT_SHARES_ASSIGNED, $this, ['by' => $member]);
	}
	
	public function skipShares(User $member) {
		if($this->status != self::STATUS_ACCEPTED) {
			throw new IllegalStateException('Cannot assign shares in a task in status '.$this->status);
		}
		//check if the evaluator is a task member
		if(!array_key_exists($member->getId(), $this->members)) {
			throw new DomainEntityUnavailableException($this, $member);
		}
		
		$this->recordThat(SharesSkipped::occur($this->id->toString(), array(
			'by' => $member->getId(),
		)));

		$this->getEventManager()->trigger(self::EVENT_SHARES_ASSIGNED, $this, ['by' => $member]);
	}
	
	public function getMembers() {
		return $this->members;
	}
	
	public function getAverageEstimation() {
		$tot = null;
		$estimationsCount = 0;
		$notEstimationCount = 0;
		foreach ($this->members as $member) {
			$estimation = isset($member['estimation']) ? $member['estimation'] : null;
			switch ($estimation) {
				case null:
					break;
				case self::NOT_ESTIMATED:
					$notEstimationCount++;
					break;
				default:
					$tot += $estimation;
					$estimationsCount++;
			}
		}
		$membersCount = count($this->members);
		if($notEstimationCount == $membersCount) {
			return self::NOT_ESTIMATED;
		}
		if(($estimationsCount + $notEstimationCount) == $membersCount || $estimationsCount > 2) {
			return round($tot / $estimationsCount, 2);
		}
		return null;
	}
	
	/**
	 * 
	 * @param id|User $user
	 */
	public function hasMember($user) {
		$key = $user instanceof User ? $user->getId() : $user;
		return isset($this->members[$key]);
	}
	/**
	 * 
	 * @param unknown $role
	 * @param id|User $user
	 */
	public function hasAs($role, $user) {
		$key = $user instanceof User ? $user->getId() : $user;
		return isset($this->members[$key]) && $this->members[$key]['role'] == $role;
	}
	
	public function getMembersCredits() {
		$credits = $this->getAverageEstimation();
		switch ($credits) {
			case null :
				return null;
			case self::NOT_ESTIMATED :
				$credits = 0;
		}
		
		$rv = array();
		foreach ($this->members as $id => $info) {
			$rv[$id] = isset($info['share']) ? round($credits * $info['share'], 2) : 0;
		}
		return $rv;
	}
	
	public function isSharesAssignmentCompleted() {
		foreach ($this->members as $member) {
			if(!isset($member['shares'])) {
				return false;
			}
		}
		return true;
	}
	
	public function getMemberRole($user){
		$key = $user instanceof User ? $user->getId() : $user;
		if(isset($this->members[$key])){
			return $this->members[$key]['role'];
		}
		return null;
	}
	
	public function getOwner(){	
		foreach ($this->members as $key=>$member){
			if($member['role']==self::ROLE_OWNER)
				return $key;
		}
		return null;	
	}
	
	protected function whenTaskCreated(TaskCreated $event)
	{
		$this->id = Uuid::fromString($event->aggregateId());
		$this->status = $event->payload()['status'];
	}
	
	protected function whenTaskOngoing(TaskOngoing $event) {
		$this->status = self::STATUS_ONGOING;
	}
	
	protected function whenTaskCompleted(TaskCompleted $event) {
		$this->status = self::STATUS_COMPLETED;
		array_walk($this->members, function(&$value, $key) {
			unset($value['shares']);
			unset($value['share']);
		});
	}
	
	protected function whenTaskAccepted(TaskAccepted $event) {
		$this->status = self::STATUS_ACCEPTED;
	}
	
	protected function whenTaskClosed(TaskClosed $event) {
		$this->status = self::STATUS_CLOSED;
	}
	
	protected function whenTaskDeleted(TaskDeleted $event) {
		$this->status = self::STATUS_DELETED;
	}
	
	protected function whenTaskUpdated(TaskUpdated $event) {
		$pl = $event->payload();
		if(array_key_exists('subject', $pl)) {
			$this->subject = $pl['subject'];
		}
	}
	
	protected function whenTaskMemberAdded(TaskMemberAdded $event) {
		$p = $event->payload();
		$id = $p['userId'];
		$this->members[$id]['role'] = $p['role'];
		if(isset($p['accountId'])) {
			$this->members[$id]['accountId'] = $p['accountId'];
		}
		if(isset($p['email'])) {
			$this->members[$id]['email'] = $p['email'];
		}
	}

	protected function whenTaskMemberRemoved(TaskMemberRemoved $event) {
		$p = $event->payload();
		$id = $p['userId'];
		unset($this->members[$id]);
	}
	
	protected function whenTaskStreamChanged(TaskStreamChanged $event) {
		$p = $event->payload();
		$this->streamId = Uuid::fromString($p['streamId']);
	}
	
	protected function whenEstimationAdded(EstimationAdded $event) {
		$p = $event->payload();
		$id = $p['by'];
		$this->members[$id]['estimation'] = $p['value'];
	}
	
	protected function whenSharesAssigned(SharesAssigned $event) {
		$p = $event->payload();
		$id = $p['by'];
		$this->members[$id]['shares'] = $p['shares'];
		$shares = $this->getMembersShare();
		if(count($shares) > 0) {
			foreach ($shares as $key => $value) {
				$this->members[$key]['share'] = $value;
			}
		}
	}
	
	protected function whenSharesSkipped(SharesSkipped $event) {
		$p = $event->payload();
		$id = $p['by'];
		foreach ($this->members as $key => $value) {
			$this->members[$id]['shares'][$key] = null;
		}
		$shares = $this->getMembersShare();
		if(count($shares) > 0) {
			foreach ($shares as $key => $value) {
				$this->members[$key]['share'] = $value;
			}
		}
	}
	
	protected function getMembersShare() {
		$rv = array();
		$evaluators = 0;
		foreach ($this->members as $evaluatorId => $info) {
			if(isset($info['shares'][$evaluatorId])) {
				$evaluators++;
				foreach($info['shares'] as $valuedId => $value) {
					$rv[$valuedId] = isset($rv[$valuedId]) ? $rv[$valuedId] + $value : $value;
				}
			}
		}
		if($evaluators > 0) {
			array_walk($rv, function(&$value, $key) use ($evaluators) {
				$value = round($value / $evaluators, 4);
			});
		}
		return $rv;
	}
}

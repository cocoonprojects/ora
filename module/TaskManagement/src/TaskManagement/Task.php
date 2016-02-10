<?php
namespace TaskManagement;

use Application\DomainEntity;
use Application\DomainEntityUnavailableException;
use Application\DuplicatedDomainEntityException;
use Application\Entity\BasicUser;
use Application\Entity\User;
use Application\IllegalStateException;
use Application\InvalidArgumentException;
use People\MissingOrganizationMembershipException;
use Rhumsaa\Uuid\Uuid;
use TaskManagement\Entity\ItemIdeaApproval;

class Task extends DomainEntity implements TaskInterface
{
	CONST NOT_ESTIMATED = -1;
	/**
	 * @var string
	 */
	protected $subject;
	/**
	 * @var string
	 */
	protected $description;
	/**
	 * @var int
	 */
	protected $status;
	/**
	 * @var Uuid
	 */
	protected $streamId;
	/**
	 * @var Uuid
	 */
	protected $organizationId;
	/**
	 */
	protected $members = [];
	/**
	 * 
	 */
	protected $organizationMembersApprovals=[];
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
	protected $acceptedAt;
	/**
	 * @var \DateTime
	 */
	protected $sharesAssignmentExpiresAt = null;

	public static function create(Stream $stream, $subject, BasicUser $createdBy, array $options = null) {
		$rv = new self();
		$rv->recordThat(TaskCreated::occur(Uuid::uuid4()->toString(), [
			'status' => self::STATUS_IDEA,
			'organizationId' => $stream->getOrganizationId(),
			'streamId' => $stream->getId(),
			'by' => $createdBy->getId()
		]));
		$rv->setSubject($subject, $createdBy);

		return $rv;
	}
	
	public function delete(BasicUser $deletedBy) {
		if($this->getStatus() >= self::STATUS_COMPLETED) {
			throw new IllegalStateException('Cannot delete a task in state '.$this->getStatus().'. Task '.$this->getId().' won\'t be deleted');
		}
		$this->recordThat(TaskDeleted::occur($this->id->toString(), array(
			'prevStatus' => $this->getStatus(),
			'by'  => $deletedBy->getId(),
		)));
	}

	/**
	 * @return int
	 */
	public function getStatus() {
		return $this->status;
	}
	
	public function execute(BasicUser $executedBy) {
		//The status IDEA is provisional
		if(!in_array($this->status, [self::STATUS_OPEN, self::STATUS_COMPLETED])) {
			throw new IllegalStateException('Cannot execute a task in '.$this->status.' state');
		}
		$this->recordThat(TaskOngoing::occur($this->id->toString(), array(
				'prevStatus' => $this->getStatus(),
				'by' => $executedBy->getId(),
		)));
		return $this;
	}
	
	public function complete(BasicUser $completedBy) {
		if(!in_array($this->status, [self::STATUS_ONGOING, self::STATUS_ACCEPTED])) {
			throw new IllegalStateException('Cannot complete a task in '.$this->status.' state');
		}
		if(is_null($this->getAverageEstimation())) {
			throw new IllegalStateException('Cannot complete a task with missing estimations by members');
		}
		$this->recordThat(TaskCompleted::occur($this->id->toString(), array(
			'prevStatus' => $this->getStatus(),
			'by' => $completedBy->getId(),
		)));
		return $this;
	}
	
	public function accept(BasicUser $acceptedBy, \DateInterval $intervalForCloseTask = null) {
		if($this->status != self::STATUS_COMPLETED) {
			throw new IllegalStateException('Cannot accept a task in '.$this->status.' state');
		}
		$this->recordThat(TaskAccepted::occur($this->id->toString(), array(
			'prevStatus' => $this->getStatus(),
			'by' => $acceptedBy->getId(),
			'intervalForCloseTask' => $intervalForCloseTask
		)));
		return $this;
	}
	
	public function close(BasicUser $closedBy) {
		if($this->status != self::STATUS_ACCEPTED) {
			throw new IllegalStateException('Cannot close a task in '.$this->status.' state');
		}
		
		$this->recordThat(TaskClosed::occur($this->id->toString(), array(
			'by' => $closedBy->getId(),
		)));
		return $this;
	}
	
	public function open(BasicUser $executedBy) {
		if(!in_array($this->status, [self::STATUS_IDEA, self::STATUS_ONGOING])) {
			throw new IllegalStateException('Cannot open a task in '.$this->status.' state');
		}
		$this->recordThat(TaskOpened::occur($this->id->toString(), array(
				'prevStatus' => $this->getStatus(),
				'by' => $executedBy->getId(),
		)));
		return $this;
	}
	
	public function archive(BasicUser $executedBy) {
		if(!in_array($this->status, [self::STATUS_IDEA])) {
			throw new IllegalStateException('Cannot archive a task in state '.$this->getStatus().'. Task '.$this->getId().' won\'t be archived');
		}
		$this->recordThat(TaskArchived::occur($this->id->toString(), array(
				'prevStatus' => $this->getStatus(),
				'by' => $executedBy->getId(),
		)));
		return $this;
	}
	/**
	 * @return string
	 */
	public function getSubject() {
		return $this->subject;
	}
	
	public function setSubject($subject, BasicUser $updatedBy) {
		$s = is_null($subject) ? null : trim($subject);
		$this->recordThat(TaskUpdated::occur($this->id->toString(), array(
			'subject' => $s,
			'by' => $updatedBy->getId(),
		)));
		return $this;
	}
	
	/**
	 * @return string
	 */
	public function getDescription() {
		return $this->description;
	}

	public function setDescription($description, BasicUser $updatedBy) {
		$d = is_null($description) ? null : trim($description);
		$this->recordThat(TaskUpdated::occur($this->id->toString(), array(
				'description' => $d,
				'by' => $updatedBy->getId(),
		)));
		return $this;
	}

	public function changeStream(Stream $stream, BasicUser $updatedBy) {
		if($this->status >= self::STATUS_COMPLETED) {
			throw new IllegalStateException('Cannot set the task stream in '.$this->status.' state');
		}
		$payload = array(
				'streamId' => $stream->getId(),
				'by' => $updatedBy->getId(),
		);
		if(!is_null($this->streamId)) {
			$payload['prevStreamId'] = $this->streamId->toString();
		}
		$this->recordThat(TaskStreamChanged::occur($this->id->toString(), $payload));
		return $this;
	}

	/**
	 * @return string
	 */
	public function getStreamId() {
		return $this->streamId->toString();
	}

	/**
	 * @return string
	 */
	public function getOrganizationId() {
		return $this->organizationId->toString();
	}
	
	/**
	 * 
	 * @param User $user
	 * @param string $role
	 * @param BasicUser $addedBy
	 * @throws IllegalStateException
	 * @throws MissingOrganizationMembershipException
	 * @throws DuplicatedDomainEntityException
	 */
	public function addMember(User $user, $role = self::ROLE_MEMBER, BasicUser $addedBy = null)
	{ 
		if($this->status >= self::STATUS_COMPLETED) {
			throw new IllegalStateException('Cannot add a member to a task in '.$this->status.' state');
		}
		if(!$user->isMemberOf($this->getOrganizationId())) {
			throw new MissingOrganizationMembershipException($this->getOrganizationId(), $user->getId());
		}
		if (array_key_exists($user->getId(), $this->members)) {
			throw new DuplicatedDomainEntityException($this, $user);
		}
		
		$by = is_null($addedBy) ? $user : $addedBy;
		
		$this->recordThat(TaskMemberAdded::occur($this->id->toString(), array(
			'userId' => $user->getId(),
			'role' => $role,
			'by' => $by->getId(),
		)));
		return $this;
	}

	/**
	 * @param User $member
	 * @param BasicUser|null $removedBy
	 */
	public function removeMember(User $member, BasicUser $removedBy = null)
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
	
	public function addEstimation($value, BasicUser $member) {
		if(!in_array($this->status, [self::STATUS_ONGOING])) {
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
	}
	
	public function addApproval($vote,BasicUser $member,$description){
		if(!in_array($this->status, [self::STATUS_IDEA])) {
			throw new IllegalStateException('Cannot add an approval to item in a status different from idea');
		}
		if (array_key_exists($member->getId(), $this->organizationMembersApprovals)) {
			throw new DuplicatedDomainEntityException($this, $user);
		}
		//TODO : remove log 
		//error_log("fino a qui ci arrivo e non ho problemi, effettuo il fire dell'evento il voto è $vote e il member è ".print_r($member,true));
		
		//optional membership check
		//error_log("asdasbeverof member id ".$member->getId());
		$this->recordThat(ApprovalCreated::occur($this->id->toString(), array(
				'by' => $member->getId(),
				'vote'	 => $vote,
				'task-id'=>$this->getId(),
				'description'=>$description,
		)));
		
		
		
		
		
		
	}
	/**
	 * 
	 * @param array $shares Map of memberId and its share for each member
	 * @param BasicUser $member
	 * @throws IllegalStateException
	 * @throws DomainEntityUnavailableException
	 * @throws InvalidArgumentException
	 */
	public function assignShares(array $shares, BasicUser $member) {
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
			throw new InvalidArgumentException('The total amount of shares must be 100');
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
	}
	
	public function skipShares(BasicUser $member) {
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
	}

	/**
	 * @param BasicUser $ex_owner
	 * @throws MissingOrganizationMembershipException
	 * @throws DomainEntityUnavailableException
	 */
	public function changeOwner(BasicUser $new_owner, BasicUser $by){
		if(!$new_owner->isMemberOf($this->getOrganizationId())) {
			throw new MissingOrganizationMembershipException($this->getOrganizationId(), $new_owner->getId());
		}
		if (!array_key_exists($new_owner->getId(), $this->members)) {
			throw new DomainEntityUnavailableException($this, $new_owner);
		}
		$ex_owner = $this->getOwner();
		if(!is_null($ex_owner)){
			$this->recordThat(OwnerRemoved::occur($this->id->toString(), array(
				'ex_owner' => $ex_owner,
				'by' => $by->getId()
			)));
		}
		$this->recordThat(OwnerAdded::occur($this->id->toString(), array(
			'new_owner' => $new_owner->getId(),
			'by' => $by->getId()
		)));
	}

	public function removeOwner(BasicUser $by){

		$ex_owner = $this->getOwner();
		if(!is_null($ex_owner)){
			$this->recordThat(OwnerRemoved::occur($this->id->toString(), array(
				'ex_owner' => $ex_owner,
				'by' => $by->getId()
			)));
		}
	}
	/**
	 * @return array
	 */
	public function getMembers() {
		return $this->members;
	}
	
	/**
	 * 
	 * @return array
	 */
	public function getApprovals(){
		return $this->organizationMembersApprovals;
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
	 * @param id|BasicUser $user
	 * @return boolean
	 */
	public function hasMember($user) {
		$key = $user instanceof BasicUser ? $user->getId() : $user;
		return isset($this->members[$key]);
	}
	/**
	 * 
	 * @param string $role
	 * @param id|BasicUser $user
	 * @return boolean
	 */
	public function hasAs($role, $user) {
		$key = $user instanceof BasicUser ? $user->getId() : $user;
		return isset($this->members[$key]) && $this->members[$key]['role'] == $role;
	}

	/**
	 * @return array|null
	 */
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
		$key = $user instanceof BasicUser ? $user->getId() : $user;
		if(isset($this->members[$key])){
			return $this->members[$key]['role'];
		}
		return null;
	}
	
	public function getOwner() {
		foreach ($this->members as $key => $member){
			if($member['role'] == self::ROLE_OWNER)
				return $key;
		}
		return null;
	}

	/**
	 * @return string
	 */
	public function getType()
	{
		return 'task';
	}

	/**
	 * @return \DateTime
	 */
	public function getCreatedAt()
	{
		return $this->createdAt;
	}

	/**
	 * @return \DateTime
	 */
	public function getAcceptedAt()
	{
		return $this->acceptedAt;
	}

	/**
	 * @return BasicUser
	 */
	public function getCreatedBy()
	{
		return $this->createdBy;
	}

	/**
	 * @return \DateTime
	 */
	public function getSharesAssignmentExpiresAt() {
		return $this->sharesAssignmentExpiresAt;
	}

	protected function whenTaskCreated(TaskCreated $event)
	{
		$this->id = Uuid::fromString($event->aggregateId());
		$p = $event->payload();
		$this->status = $p['status'];
		$this->organizationId = Uuid::fromString($p['organizationId']);
		$this->streamId = Uuid::fromString($p['streamId']);
		$this->createdAt = $event->occurredOn();
		$this->createdBy = BasicUser::createBasicUser($p['by']);
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
		$this->acceptedAt = $event->occurredOn();
		if(isset($event->payload()['intervalForCloseTask'])) {
			$sharesAssignmentExpiresAt = clone $event->occurredOn();
			$sharesAssignmentExpiresAt->add($event->payload()['intervalForCloseTask']);
			$this->sharesAssignmentExpiresAt = $sharesAssignmentExpiresAt;
		}
	}
	
	protected function whenTaskClosed(TaskClosed $event) {
		$this->status = self::STATUS_CLOSED;
	}
	
	protected function whenTaskDeleted(TaskDeleted $event) {
		$this->status = self::STATUS_DELETED;
	}
	
	protected function whenTaskOpened(TaskOpened $event) {
		$this->status = self::STATUS_OPEN;
	}
	
	protected function whenTaskArchived(TaskArchived $event) {
		$this->status = self::STATUS_ARCHIVED;
	}
	
	protected function whenTaskUpdated(TaskUpdated $event) {
		$pl = $event->payload();
		if(array_key_exists('subject', $pl)) {
			$this->subject = $pl['subject'];
		}
		if(array_key_exists('description', $pl)) {
			$this->description = $pl['description'];
		}
	}
	
	protected function whenTaskMemberAdded(TaskMemberAdded $event) {
		$p = $event->payload();
		$id = $p['userId'];
		$this->members[$id]['id'] = $id;
		$this->members[$id]['role'] = $p['role'];
		$this->members[$id]['createdAt'] = $event->occurredOn();
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
		$this->members[$id]['estimatedAt'] = $event->occurredOn();
	}
	
	//TODO : implementare catcher
	protected function whenApprovalCreated(ApprovalCreated $event){
		$p =$event->payload();
		$id = $p['by'];
		$this->organizationMembersApprovals[$id]['approval']=$p['vote'];
		$this->organizationMembersApprovals[$id]['approvalGeneratedAt']=$event->occurredOn();
	//	error_log("sono arrivato alla gestione dell'evento approval created evento --> ".print_r($event,true));
// 		$p = $event->payload();
// 		$id = $p['by'];
// 		$vote = $p['vote'];
// 		$createdAt= $event->occurredOn();
		
// 		//$this->members[$id]['approval']=$p['vote'];
// 		//$this->members[$id]['createdAt']=$event->occurredOn();
// 		$approval = new ItemIdeaApproval($vote, $createdAt);
	
// 		$approval->setItem($this);
		
// 		$approval->setVoter($user)
		
		
	}
	
	protected function whenSharesAssigned(SharesAssigned $event) {
		$p = $event->payload();
		$id = $p['by'];
		$this->members[$id]['shares'] = $p['shares'];
		$shares = $this->getMembersShare();
		if(count($shares) > 0) {
			foreach ($shares as $key => $value) {
				$this->members[$key]['share'] = $value;
				if(isset($this->members[$key]['shares'][$key])) {
					$this->members[$key]['delta'] = $this->members[$key]['shares'][$key] - $value;
				}
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

	/**
	 * Returns the string identifier of the Resource
	 *
	 * @return string
	 */
	public function getResourceId(){
		return 'Ora\Task';
	}
	/**
	 *
	 * @param id|BasicUser $user
	 * @return boolean
	 */
	public function areSharesAssignedFromMember($user){
		$key = $user instanceof BasicUser ? $user->getId() : $user;
		return isset($this->members[$key]['shares']);
	}

	public function assignCredits(BasicUser $by){
		$this->recordThat(CreditsAssigned::occur($this->id->toString(), array(
				'credits' => $this->getMembersCredits(),
				'by'=>$by->getId()
		)));
		return $this;
	}

	protected function whenCreditsAssigned(CreditsAssigned $event){
	}

	protected function whenOwnerAdded(OwnerAdded $event){
		$p = $event->payload();
		$new_owner = $p['new_owner'];
		$this->members[$new_owner]['role'] = self::ROLE_OWNER;
	}

	protected function whenOwnerRemoved(OwnerRemoved $event){
		$p = $event->payload();
		$ex_owner = $p['ex_owner'];
		$this->members[$ex_owner]['role'] = self::ROLE_MEMBER;
	}

	public function isSharesAssignmentExpired(\DateTime $ref){
		if(is_null($this->sharesAssignmentExpiresAt)){
			return false;
		}
		return $ref > $this->sharesAssignmentExpiresAt;
	}
}

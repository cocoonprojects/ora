<?php
namespace TaskManagement\Entity;

use Application\Entity\BasicUser;
use Application\Entity\EditableEntity;
use Application\Entity\User;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use TaskManagement\TaskInterface;

/**
 * @ORM\Entity @ORM\Table(name="tasks")
 * @ORM\InheritanceType("JOINED")
 * @ORM\DiscriminatorColumn(name="type", type="string")
 * TODO: If no DiscriminatorMap annotation is specified, doctrine uses lower-case class name as default values. Remove
 * TYPE use
 */
class Task extends EditableEntity implements TaskInterface
{
	/**
	 * @ORM\Column(type="string")
	 * @var string
	 */
	private $subject;
	
	/**
	 * @ORM\Column(type="string", nullable=true)
	 * @var string
	 */
	private $description;

	/**
	 * @ORM\Column(type="integer")
	 * @var int
	 */
	private $status;
	
	/**
	 * @ORM\ManyToOne(targetEntity="Stream")
	 * @ORM\JoinColumn(name="stream_id", referencedColumnName="id", nullable=false)
	 * @var Stream
	 */
	private $stream;

	/**
	 * @ORM\OneToMany(targetEntity="TaskMember", mappedBy="task", cascade={"PERSIST", "REMOVE"}, orphanRemoval=TRUE, indexBy="member_id")
	 * @ORM\OrderBy({"createdAt" = "ASC"})
	 * @var TaskMember[]
	 */
	private $members;
	
	/**
	 * @ORM\OneToMany(targetEntity="ItemIdeaApproval", mappedBy="item", cascade={"PERSIST", "REMOVE"}, orphanRemoval=TRUE)
	 * @ORM\OrderBy({"createdAt" = "ASC"})
	 * @var Approval[]
	 */
	private $approvals;
	
	/**
	 * @ORM\OneToMany(targetEntity="ItemCompletedAcceptance", mappedBy="item", cascade={"PERSIST", "REMOVE"}, orphanRemoval=TRUE)
	 * @ORM\OrderBy({"createdAt" = "ASC"})
	 * @var Acceptance[]
	 */
	private $acceptances;
	
	/**
 	 * @ORM\Column(type="datetime", nullable=true)
	 * @var \DateTime
	 */
	protected $acceptedAt;
	
	/**
	 * @ORM\Column(type="datetime", nullable=true)
	 * @var \DateTime
	 */
	protected $sharesAssignmentExpiresAt;

	public function __construct($id, Stream $stream) {
		parent::__construct($id);
		$this->stream = $stream;
		$this->members = new ArrayCollection();
		$this->approvals = new ArrayCollection();
		$this->acceptances = new ArrayCollection();
	}

	/**
	 * @return int
	 */
	public function getStatus() {
		return $this->status;
	}

	public function setStatus($status) {
		$this->status = $status;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getSubject() {
		return $this->subject;
	}
	
	public function setSubject($subject) {
		$this->subject = $subject;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getDescription() {
		return $this->description;
	}

	public function setDescription($description) {
		$this->description = $description;
		return $this;
	}

	/**
	 * @return Stream
	 */
	public function getStream() {
		return $this->stream;
	}

	/**
	 * @return string
	 */
	public function getStreamId() {
		if($this->stream) {
			return $this->stream->getId();
		}
		return null;
	}

	/**
	 * @param Stream $stream
	 * @return $this
	 */
	public function setStream(Stream $stream) {
		$this->stream = $stream;
		return $this;
	}

	public function getOrganizationId() {
		return $this->stream->getOrganization()->getId();
	}
	
	public function addMember(User $user, $role, BasicUser $by, \DateTime $when) {
		$taskMember = new TaskMember($this, $user, $role);
		$taskMember->setCreatedAt($when)
			->setCreatedBy($by)
			->setMostRecentEditAt($when)
			->setMostRecentEditBy($by);
		$this->members->set($user->getId(), $taskMember);
		return $this;
	}
	
	public function addApproval (Vote $vote, BasicUser $by, \DateTime $when ,$description){
		$approval = new ItemIdeaApproval($vote, $when);
		$approval->setCreatedBy($by);
		$approval->setCreatedAt($when);
		$approval->setItem($this);
		$approval->setVoter($by);
		$approval->setMostRecentEditAt($when);
		$approval->setMostRecentEditBy($by);
		$approval->setDescription($description);
		$this->approvals->set($approval->getId(), $approval);
		
		return $this;
	}
	
	public function addAcceptance (Vote $vote, BasicUser $by, \DateTime $when ,$description){
		$acceptance = new ItemCompletedAcceptance($vote, $when);
		$acceptance->setCreatedBy($by);
		$acceptance->setCreatedAt($when);
		$acceptance->setItem($this);
		$acceptance->setVoter($by);
		$acceptance->setMostRecentEditAt($when);
		$acceptance->setMostRecentEditBy($by);
		$acceptance->setDescription($description);
		$this->acceptances->set($acceptance->getId(), $acceptance);
		
		return $this;
	}
	
	public function removeAcceptances(){
		$this->acceptances->clear();		
		return $this;
	}
	
	/**
	 * 
	 * @param id|User $member
	 * @return $this
	 */
	public function removeMember($member) {
		$id = $member instanceof User ? $member->getId() : $member;
		$this->members->remove($id);
		return $this;
	}
	
	/**
	 * 
	 * @param id|BasicUser $user
	 * @return TaskMember|NULL
	 */
	public function getMember($user) {
		$key = $user instanceof BasicUser ? $user->getId() : $user;
			return $this->members->get($key);
	}

	/**
	 * @return null|TaskMember
	 */
	public function getOwner() {
		foreach ($this->members as $key => $member){
			if($member->getRole() == self::ROLE_OWNER)
				return $member;
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
		return $this->members->containsKey($key);
	}

	/**
	 * @return TaskMember[]
	 */
	public function getMembers() {
		return $this->members->toArray();
	}
	
	/**
	 * @return Approval[] 
	 */
	public function getApprovals(){
		return $this->approvals->toArray();
	}
	
	/**
	 * @return Approval[] 
	 */
	public function getAcceptances(){
		return $this->acceptances->toArray();
	}

	/**
	 * @return string
	 */
	public function getType(){
		return 'task';
	}
	
	/**
	 * TODO: da rimuovere, deve leggere un valore già calcolato. Il calcolo sta nel write model
	 * @return string|number|NULL
	 */
	public function getAverageEstimation() {
		$tot = null;
		$estimationsCount = 0;
		$notEstimationCount = 0;
		foreach ($this->members as $member) {
			$estimation = $member->getEstimation()->getValue();
			switch ($estimation) {
			case null:
				break;
			case Estimation::NOT_ESTIMATED:
				$notEstimationCount++;
				break;
			default:
				$tot += $estimation;
				$estimationsCount++;
			}
		}
		if($notEstimationCount == count($this->members)) {
			return Estimation::NOT_ESTIMATED;
		}
		if(($estimationsCount + $notEstimationCount) == count($this->members) || $estimationsCount > 2) {
			return round($tot / $estimationsCount, 2);
		}
		return null;
	}
	
	public function resetShares() {
		foreach ($this->members as $member) {
			$member->resetShares();
			$member->setShare(null, new \DateTime());
		}
	}
	
	public function updateMembersShare(\DateTime $when) {
		$shares = $this->getMembersShare();
		foreach ($shares as $key => $value) {
			$this->members->get($key)->setShare($value, $when);
		}
	}
	
	private function getMembersShare() {
		$rv = [];
		foreach ($this->members as $member) {
			$rv[$member->getMember()->getId()] = null;
		}
		$evaluators = 0;
		foreach ($this->members as $evaluatorId => $info) {
			if(count($info->getShares()) > 0 && $info->getShareValueOf($info) !== null) {
				$evaluators++;
				foreach($info->getShares() as $valuedId => $share) {
					$rv[$valuedId] = isset($rv[$valuedId]) ? $rv[$valuedId] + $share->getValue() : $share->getValue();
				}
			}
		}
		if($evaluators > 0) {
			array_walk($rv, function(&$value) use ($evaluators) {
				$value = round($value / $evaluators, 4);
			});
		}
		return $rv;
	}

	public function getResourceId(){
		return 'Ora\Task';
	}
	
	public function getMemberRole($user)
	{
		$memberFound = $this->getMember($user);
		if($memberFound instanceof TaskMember) {
			return $memberFound->getRole();
		}
		return null;
	}

	/**
	 * @return \DateTime
	 */
	public function getAcceptedAt() {
		return $this->acceptedAt;
	}
	
	public function setAcceptedAt(\DateTime $date) {
		$this->acceptedAt = $date;
	}
	
	public function getSharesAssignmentExpiresAt() {
		return $this->sharesAssignmentExpiresAt;
	}
	
	public function setSharesAssignmentExpiresAt(\DateTime $date) {
		$this->sharesAssignmentExpiresAt = $date;
	}
	
	public function resetAcceptedAt(){
		$this->acceptedAt = null;
	}
	
	
	/**
	 * Retrieve members that haven't assigned any share
	 *
	 * @return TaskMember[]
	 */
	public function findMembersWithEmptyShares()
	{
		return array_filter($this->getMembers(), function($member) {
			return empty($member->getShares());
		});
	}

	/**
	 * @return TaskMember[]
	 */
	public function findMembersWithNoApproval()
	{
		$membersWhoVoted = [];
		foreach ($this->getApprovals() as $approval) {
			$membersWhoVoted[] = $approval->getVoter()->getId();
		}

		return array_filter($this->getMembers(), function($member) use ($membersWhoVoted) {
			return !in_array($member->getUser()->getId(), $membersWhoVoted);
		});
	}

	/**
	 * @return TaskMember[]
	 */
	public function findMembersWithNoEstimation()
	{
		return array_filter($this->getMembers(), function($member) {
			return $member->getEstimation() == null || $member->getEstimation()->getValue() == null;
		});
	}

	/**
	 * @param id|BasicUser $user
	 * @return boolean|null
	 */
	public function areSharesAssignedFromMember($user) {
		$taskMember = $this->getMember($user);
		if($taskMember != null){
			return !empty($taskMember->getShares());
		}
		return false;
	}
	
	/**
	 * @param id|BasicUser $user
	 * @return boolean
	 */
	public function isIdeaVotedFromMember($user){
		$approvals = $this->getApprovals();
		if($approvals!=null){
			foreach ($approvals as $approval){
				if($approval->getVoter()->getId() == $user->getId())
					return true;
			}
		}
		return false;
	}	
	/**
	 * @param id|BasicUser $user
	 * @return boolean
	 */
	public function isCompletedVotedFromMember($user){
		$acceptances = $this->getAcceptances();
		if($acceptances!=null){
			foreach ($acceptances as $acceptance){
				if($acceptance->getVoter()->getId() == $user->getId())
					return true;
			}
		}
		return false;
	}

	public function isSharesAssignmentCompleted() {
		foreach ($this->members as $taskMember) {
			if(empty($taskMember->getShares())) {
				return false;
			}
		}
		return true;
	}

	public function isSharesAssignmentExpired(\DateTime $ref){
		if(is_null($this->sharesAssignmentExpiresAt)){
			return false;
		}
		return $ref > $this->sharesAssignmentExpiresAt;
	}
}
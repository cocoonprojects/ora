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
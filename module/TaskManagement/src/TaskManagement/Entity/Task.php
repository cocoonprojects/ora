<?php
namespace TaskManagement\Entity;

use Zend\Permissions\Acl\Resource\ResourceInterface;
use Doctrine\ORM\Mapping AS ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Rhumsaa\Uuid\Uuid;
use Application\IllegalStateException;
use Application\DuplicatedDomainEntityException;
use Application\DomainEntityUnavailableException;
use Application\Entity\User;
use Application\Entity\EditableEntity;

/**
 * @ORM\Entity @ORM\Table(name="tasks")
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="type", type="string")
 *
 */

// If no DiscriminatorMap annotation is specified, doctrine uses lower-case class name as default values

class Task extends EditableEntity implements ResourceInterface
{	
	CONST STATUS_IDEA = 0;
	CONST STATUS_OPEN = 10;
	CONST STATUS_ONGOING = 20;
	CONST STATUS_COMPLETED = 30;
	CONST STATUS_ACCEPTED = 40;
	CONST STATUS_CLOSED = 50;
	CONST STATUS_DELETED = -10;
	
	CONST ROLE_MEMBER = 'member';
	CONST ROLE_OWNER  = 'owner';

	CONST TYPE = 'task';

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
	 * @ORM\OneToMany(targetEntity="TaskMember", mappedBy="task", cascade={"PERSIST", "REMOVE"}, indexBy="member_id")
	 * @var TaskMember[]
	 */
	private $members;
	
	/**
<<<<<<< HEAD
	 * @ORM\Column(type="datetime", nullable=true)
=======
	 * @ORM\Column(type="datetime")
>>>>>>> created timebox for share assignment
	 * @var \DateTime
	 */
	protected $acceptedAt;
	
	
	public function __construct($id) 
	{
		$this->id = $id;
		$this->members = new ArrayCollection();
	}
	
	public function getStatus() {
		return $this->status;
	}
	
	public function getSubject() {
		return $this->subject;
	}
	
	public function setSubject($subject) {
		$this->subject = $subject;
		return $this;
	}
	
	public function getStream() {
		return $this->stream;
	}
	
	public function setStream(Stream $stream) {
		$this->stream = $stream;
		return $this;
	}
	
	public function setStatus($status) {
		$this->status = $status;
		return $this;
	}
	
	public function addMember(User $user, $role, User $by, \DateTime $when) {
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
	 * @param id|TaskMember $member
	 */
	public function removeMember($member) {
		if($member instanceof TaskMember) {
			$this->members->removeElement($member);
		} else {
			$this->members->remove($member);
		}
		return $this;
	}
	
	/**
	 * 
	 * @param id|User $user
	 * @return TaskMember|NULL
	 */
	public function getMember($user) {
		$key = $user instanceof User ? $user->getId() : $user;
			return $this->members->get($key);
	}
	
	/**
	 * 
	 * @param id|User $user
	 * @return boolean
	 */
	public function hasMember($user) {
		$key = $user instanceof User ? $user->getId() : $user;
		return $this->members->containsKey($key);
	}

	/**
	 * @return TaskMember[]
	 */
	public function getMembers() {
		return $this->members->toArray();
	}
	
	public function getType(){
			$c = get_called_class();
			return $c::TYPE;
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
		$rv = array();
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
			array_walk($rv, function(&$value, $key) use ($evaluators) {
				$value = round($value / $evaluators, 4);
			});
		}
		return $rv;
	}

	public function getResourceId(){
		return "Ora\Task";
	}
	
	public function getMemberRole($user){
		
		$memberFound = $this->getMember($user);

		if($memberFound instanceof TaskMember){
			return $memberFound->getRole();
		}
		
		return null;
	}
	
	
	public function getAcceptedAt() {
		return $this->acceptedAt;
	}
	
	public function setAcceptedAt(\DateTime $date) {
		$this->acceptedAt = $date;
	}
	
}
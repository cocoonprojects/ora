<?php

namespace Ora\TaskManagement;

use Doctrine\ORM\Mapping AS ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Ora\DomainEntity;
use Rhumsaa\Uuid\Uuid;
use Ora\ProjectManagement\Project;
use Ora\User\Profile;
use Ora\User\MasterData;
use Ora\IllegalStateException;
use Ora\DuplicatedDomainEntityException;
use Ora\DomainEntityUnavailableException;

/**
 * @ORM\Entity @ORM\Table(name="tasks")
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="type", type="string")
 * @ORM\DiscriminatorMap({
 *                    "task" = "Ora\TaskManagement\Task"
 *                    })
 * @author Giannotti Fabio
 *
 */

// TODO: Aggiungere questo rigo a DiscriminatorMap
//"kanbanizeTask" = "Ora\Kanbanize\KanbanizeTask"

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
	 * @ORM\ManyToOne(targetEntity="Ora\ProjectManagement\Project")
	 */
	private $project;
	
	/**
	 * @ORM\ManyToMany(targetEntity="Ora\User\Profile")
	 * @ORM\JoinTable(name="teams",
	 *      joinColumns={@ORM\JoinColumn(name="task_id", referencedColumnName="id")},
	 *      inverseJoinColumns={@ORM\JoinColumn(name="user_id", referencedColumnName="id")}
	 *      )
	 */
	private $members;
	
	public function __construct() 
	{
		$this->status = self::STATUS_ONGOING;
		$this->members = new ArrayCollection();
		
	}
	
	public static function create(Project $project, $subject, MasterData $createdBy, \DateTime $createdAt = null) {
		$d = $createdAt == null ? new \DateTime() : $createdAt;
		$id = Uuid::uuid4();
		$rv = new self();
		$creator = $createdBy->toProfile();
		$payload = array(
			'createdAt' => $d,
			'createdBy' => $creator,
			'status' => $rv->getStatus(),
			'subject' => $subject,
			'members' => array($creator),
			'project' => $project,
		);
		$rv->recordThat(TaskCreated::occur($id->toString(), $payload));
		return $rv;
	}
	
	public function delete(MasterData $deletedBy) {
		if($this->getStatus() == Task::STATUS_COMPLETED || $this->getStatus() == Task::STATUS_ACCEPTED) {
			throw new IllegalStateException('Cannot delete a task in state '.$this->getStatus().'. Task '.$this->getId()->toString().' won\'t be deleted');
		}
		$this->recordThat(TaskDeleted::occur($this->id->toString(), array(
			'prevStatus' => $this->getStatus(),
			'deletedBy'  => $deletedBy->toProfile(),
		)));
	}
	
	public function getStatus() {
	    return $this->status;
	}
	
	public function complete(MasterData $completedBy) {
		$this->recordThat(TaskCompleted::occur($this->id->toString(), array(
			'prevStatus' => $this->getStatus(),
			'completedBy' => $completedBy->toProfile(),
		)));
	}
	
	public function accept(MasterData $acceptedBy) {
		$this->recordThat(TaskAccepted::occur($this->id->toString(), array(
			'prevStatus' => $this->getStatus(),
			'acceptedBy' => $acceptedBy->toProfile(),
		)));
	}
	
	public function getSubject() {
		return $this->subject;
	}
	
	public function setSubject($subject, MasterData $updatedBy) {
		$this->recordThat(TaskUpdated::occur($this->id->toString(), array(
			'subject' => $subject,
			'updatedBy' => $updatedBy->toProfile(),
		)));
	}
	
	public function getProject() {
	    return $this->project;
	}
	
	public function addMember(MasterData $user, MasterData $addedBy, $role = self::ROLE_MEMBER)
	{
		if($this->status == self::STATUS_COMPLETED || $this->status == self::STATUS_ACCEPTED) {
			throw new IllegalStateException('Cannot add a member to a task in '.$this->status.' state');
		}
        if ($this->members->containsKey($user->getId()->toString())) {
        	throw new DuplicatedDomainEntityException($this, $user); 
        }
        $this->recordThat(MemberAdded::occur($this->id->toString(), array(
        	'user' => $user->toProfile(),
        	'addedBy' => $addedBy->toProfile(),
        )));
	}
	
	public function removeMember(MasterData $member, MasterData $removedBy)
	{
		if($this->status == self::STATUS_COMPLETED || $this->status == self::STATUS_ACCEPTED) {
			throw new IllegalStateException('Cannot remove a member from a task in '.$this->status.' state');
		}
		// TODO: Integrare controllo per cui è possibile effettuare l'UNJOIN
        // solo nel caso in cui non sia stata ancora effettuata nessuna stima
		if (!$this->members->containsKey($member->getId()->toString())) {
        	throw new DomainEntityUnavailableException($this, $member); 
        }
		$this->recordThat(MemberRemoved::occur($this->id->toString(), array(
        	'user' => $member->toProfile(),
        	'removedBy' => $removedBy->toProfile(),
        )));
	}
	
	public function getMembers() {
	    return $this->members;
	}
	
	public function serialize()
	{
		$data = array(
			'id' => $this->id->toString(),
			'subject' => $this->subject,
			'status' => $this->status,
			'members' => $this->members->toArray(),
		);
	    return serialize($data); 
	}
	
	public function unserialize($encodedData)
	{
	    $data = userialize($encodedData);
	    $this->id = Uuid::fromString($data['id']);
	    $this->subject = $data['subject'];
	    $this->status = $data['status'];
	    $this->members = $data['members'];
	}
		
	protected function whenTaskCreated(TaskCreated $event)
	{
		$this->id = Uuid::fromString($event->aggregateId());
		$p = $event->payload();
		$this->createdAt = $p['createdAt'];
		$this->createdBy = $p['createdBy'];
		$this->mostRecentEditAt = $this->createdAt;
		$this->mostRecentEditBy = $this->createdBy;
		if(isset($p['status'])) {
			$this->status = $p['status'];
		}
		$this->subject = $p['subject'];
		$this->project = $p['project'];
		foreach ($p['members'] as $member) {
			$this->members->add($member);
		}
	}
	
	protected function whenTaskCompleted(TaskCompleted $event) {
		$this->status = self::STATUS_COMPLETED;
		$this->mostRecentEditAt = $event->occurredOn();
		$this->mostRecentEditBy = $event->payload()['completedBy'];
	}
	
	protected function whenTaskAccepted(TaskAccepted $event) {
		$this->status = self::STATUS_ACCEPTED;
		$this->mostRecentEditAt = $event->occurredOn();
		$this->mostRecentEditBy = $event->payload()['acceptedBy'];
	}
	
	protected function whenTaskDeleted(TaskDeleted $event) {
		$this->status = self::STATUS_DELETED;
		$this->mostRecentEditAt = $event->occurredOn();
		$this->mostRecentEditBy = $event->payload()['deletedBy'];
	}
	
	protected function whenTaskUpdated(TaskUpdated $event) {
		if(isset($event->payload()['subject'])) {
			$this->subject = $event->payload()['subject'];
		}
		$this->mostRecentEditAt = $event->occurredOn();
		$this->mostRecentEditBy = $event->payload()['updatedBy'];
	}
	
	protected function whenMemberAdded(MemberAdded $event) {
		$p = $event->payload();
		$this->members->set($p['user']->getId()->toString(), $p['user']);
		$this->mostRecentEditAt = $event->occurredOn();
		$this->mostRecentEditBy = $p['addedBy'];
	}

	public function whenMemberRemoved(MemberRemoved $event) {
		$p = $event->payload();
		$this->members->remove($p['user']->getId()->toString());
		$this->mostRecentEditAt = $event->occurredOn();
		$this->mostRecentEditBy = $p['removedBy'];
	}
	
}
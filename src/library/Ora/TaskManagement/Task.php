<?php

namespace Ora\TaskManagement;

use Doctrine\ORM\Mapping AS ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Rhumsaa\Uuid\Uuid;
use Ora\DomainEntity;
use Ora\IllegalStateException;
use Ora\DuplicatedDomainEntityException;
use Ora\DomainEntityUnavailableException;
use Ora\ProjectManagement\Project;
use Ora\User\User;

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
	 * @ORM\ManyToMany(targetEntity="Ora\User\User")
	 * @ORM\JoinTable(name="task_users",
	 *      joinColumns={@ORM\JoinColumn(name="task_id", referencedColumnName="id")},
	 *      inverseJoinColumns={@ORM\JoinColumn(name="user_id", referencedColumnName="id")}
	 *      )
	 */
	private $members;
	
	protected function __construct() 
	{
		$this->members = new ArrayCollection();
	}
	
	public static function create(Project $project, $subject, User $createdBy) {
		$rv = new self();
		$rv->id = Uuid::uuid4();
		$rv->status = self::STATUS_ONGOING;
		$rv->subject = $subject;
		$rv->recordThat(TaskCreated::occur($rv->id->toString(), array(
				'task' => $rv,
				'createdBy' => $createdBy,
		)));
		$rv->changeProject($project, $createdBy);
		$rv->addMember($createdBy, $createdBy);
		return $rv;
	}
	
	public function delete(User $deletedBy) {
		if($this->getStatus() == Task::STATUS_COMPLETED || $this->getStatus() == Task::STATUS_ACCEPTED) {
			throw new IllegalStateException('Cannot delete a task in state '.$this->getStatus().'. Task '.$this->getId()->toString().' won\'t be deleted');
		}
		$this->recordThat(TaskDeleted::occur($this->id->toString(), array(
			'task' => $this,
			'prevStatus' => $this->getStatus(),
			'deletedBy'  => $deletedBy,
		)));
	}
	
	public function getStatus() {
	    return $this->status;
	}
	
	public function complete(User $completedBy) {
		$this->recordThat(TaskCompleted::occur($this->id->toString(), array(
			'task' => $this,
			'prevStatus' => $this->getStatus(),
			'completedBy' => $completedBy,
		)));
	}
	
	public function accept(User $acceptedBy) {
		$this->recordThat(TaskAccepted::occur($this->id->toString(), array(
			'task' => $this,
			'prevStatus' => $this->getStatus(),
			'acceptedBy' => $acceptedBy,
		)));
	}
	
	public function getSubject() {
		return $this->subject;
	}
	
	public function setSubject($subject, User $updatedBy) {
		$this->recordThat(TaskUpdated::occur($this->id->toString(), array(
			'task' => $this,
			'subject' => $subject,
			'updatedBy' => $updatedBy,
		)));
	}
	
	public function changeProject(Project $project, User $updatedBy) {
		if($this->status == self::STATUS_COMPLETED || $this->status == self::STATUS_ACCEPTED) {
			throw new IllegalStateException('Cannot set the task project in '.$this->status.' state');
		}
		$this->recordThat(ProjectChanged::occur($this->id->toString(), array(
				'task' => $this,
				'project' => $project,
				'updatedBy' => $updatedBy,
		)));
	}
	
	public function getProject() {
	    return $this->project;
	}
	
	public function addMember(User $user, User $addedBy, $role = self::ROLE_MEMBER)
	{
		if($this->status == self::STATUS_COMPLETED || $this->status == self::STATUS_ACCEPTED) {
			throw new IllegalStateException('Cannot add a member to a task in '.$this->status.' state');
		}
        if ($this->members->containsKey($user->getId()->toString())) {
        	throw new DuplicatedDomainEntityException($this, $user); 
        }
        $this->recordThat(MemberAdded::occur($this->id->toString(), array(
        	'task' => $this,
        	'user' => $user,
        	'addedBy' => $addedBy,
        )));
	}
	
	public function removeMember(User $member, User $removedBy)
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
        	'task' => $this,
			'user' => $member,
        	'removedBy' => $removedBy,
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
		$task = $event->payload()['task'];
		$createdBy = $event->payload()['createdBy'];
		$this->createdAt = $event->occurredOn();
		$this->createdBy = $createdBy;
		$this->mostRecentEditAt = $this->createdAt;
		$this->mostRecentEditBy = $this->createdBy;
		$this->status = $task->status;
		$this->subject = $task->subject;
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
	
	public function whenProjectChanged(ProjectChanged $event) {
		$p = $event->payload();
		$this->project = $p['project'];
		$this->mostRecentEditAt = $event->occurredOn();
		$this->mostRecentEditBy = $p['updatedBy'];
	}
}
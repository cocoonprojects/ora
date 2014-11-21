<?php

namespace Ora\ReadModel;

use Doctrine\ORM\Mapping AS ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Rhumsaa\Uuid\Uuid;
use Ora\IllegalStateException;
use Ora\DuplicatedDomainEntityException;
use Ora\DomainEntityUnavailableException;
use Ora\User\User;

/**
 * @ORM\Entity @ORM\Table(name="tasks")
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="type", type="string")
 * @author Giannotti Fabio
 *
 */

// If no DiscriminatorMap annotation is specified, doctrine uses lower-case class name as default values

class Task extends DomainEntity
{	
    CONST STATUS_IDEA = 0;
    CONST STATUS_OPEN = 10;
    CONST STATUS_ONGOING = 20;
    CONST STATUS_COMPLETED = 30;
    CONST STATUS_ACCEPTED = 40;
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
	 * @ORM\ManyToOne(targetEntity="Ora\ReadModel\Project")
	 * @ORM\JoinColumn(name="project_id", referencedColumnName="id", nullable=false)
	 */
	private $project;
	
   /**
    * @ORM\OneToMany(targetEntity="Ora\ReadModel\TaskMember", mappedBy="task", cascade={"PERSIST", "REMOVE"})
    */
	private $members;
	
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
	}
	
	public function getProject() {
	    return $this->project;
	}
	
	public function setProject(Project $project) {
		$this->project = $project;
		return $this->project;
	}
	
	public function getMembers() {
	    return $this->members;
	}
	
	public function setStatus($status) {
		$this->status = $status;
		return $this->status;
	}
	
    public function addMember(User $user, $role) {

        $taskMember = new TaskMember($this, $user, $role);
		$this->members->add($taskMember);
		return $this->members;
	}
	
	public function removeMemberById($key) {
		$this->members->remove($key);
	}
	
	public function removeMember(TaskMember $member) {
		$this->members->removeElement($member);
    }

    public function getType(){

         $c = get_called_class();
         return $c::TYPE;
    }
}

<?php

namespace Ora\ReadModel;

use Doctrine\ORM\Mapping AS ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Rhumsaa\Uuid\Uuid;
use Ora\IllegalStateException;
use Ora\DuplicatedDomainEntityException;
use Ora\DomainEntityUnavailableException;
use Ora\User\User;
use Zend\Permissions\Acl\Resource\ResourceInterface;
use Ora\TaskManagement\ReadableTask;

/**
 * @ORM\Entity @ORM\Table(name="tasks")
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="type", type="string")
 * @author Giannotti Fabio
 *
 */

// If no DiscriminatorMap annotation is specified, doctrine uses lower-case class name as default values

class Task extends EditableEntity implements ResourceInterface, ReadableTask
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
	 * @ORM\ManyToOne(targetEntity="Ora\ReadModel\Stream")
	 * @ORM\JoinColumn(name="stream_id", referencedColumnName="id", nullable=false)
	 */
	private $stream;

	/**
	 * @ORM\OneToMany(targetEntity="Ora\ReadModel\TaskMember", mappedBy="task", cascade={"PERSIST", "REMOVE"}, indexBy="member_id")
	 * @var TaskMember[]
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
	
	public function getStream() {
	    return $this->stream;
	}
	
	public function setStream(Stream $stream) {
		$this->stream = $stream;
		return $this->stream;
	}
	
	public function setStatus($status) {
		$this->status = $status;
		return $this->status;
	}
	
    public function addMember(User $user, $role, User $by, \DateTime $when) {
        $taskMember = new TaskMember($this, $user, $role);
        $taskMember->setCreatedAt($when);
        $taskMember->setCreatedBy($by);
        $taskMember->setMostRecentEditAt($when);
        $taskMember->setMostRecentEditBy($by);
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
			$this->members->remove($key);
		}
		return $this;
	}
    
	/**
	 * 
	 * @param id|User $user
	 * @return \Ora\ReadModel\TaskMember|NULL
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
    public function getEstimation() {
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
    		return $tot / $estimationsCount;
    	}
    	return null;
    }
    

	public function updateMembersShare() {
		$shares = $this->getMembersShare();
		foreach ($shares as $key => $value) {
			$this->members->get($key)->setShare($value);
		}
	}
	
	private function getMembersShare() {
		$rv = array();
		$evaluators = 0;
		foreach ($this->members as $evaluatorId => $info) {
			if(count($info->getShares()) > 0) {
				$evaluators++;
				foreach($info->getShares() as $valuedId => $share) {
					$rv[$valuedId] = isset($rv[$valuedId]) ? $rv[$valuedId] + $share->getValue() : $share->getValue();
				}
			}
		}
		if($evaluators > 0) {
			array_walk($rv, function(&$value, $key) use ($evaluators) {
				$value = $value / $evaluators;
			});
		}
		return $rv;
	}

    public function getResourceId(){
    	return get_class($this);
    }
	
    public function getReadableMembers(){
    	
    	$membersArray = array();
    	
    	foreach($this->members as $taskMember){
    		
    		$memberId = $taskMember->getMember()->getId();
    		$memberRole = $taskMember->getRole();
    		
    		$membersArray[$memberId] = $memberRole;
    	}
    	
    	return $membersArray;
    }
    
    public function getReadableEstimation($memberId){
    	
    	$estimation = $this->members->get($memberId) instanceof TaskMember ? $this->members->get($memberId)->getEstimation() : NULL;
    	
    	return $estimation instanceof Estimation ? $estimation->getValue() : NULL;
    }
}

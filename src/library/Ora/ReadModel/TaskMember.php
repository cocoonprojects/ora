<?php
namespace Ora\ReadModel;

use Doctrine\ORM\Mapping AS ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Ora\User\User;
use Ora\ReadModel\Estimation;

/**
 * @ORM\Entity @ORM\Table(name="task_members")
 * @author Tilli Mario
 *
 */
class TaskMember {	

    CONST ROLE_MEMBER = 'member';
    CONST ROLE_OWNER  = 'owner';
	
	/** 
     * @ORM\Id 
     * @ORM\ManyToOne(targetEntity="Ora\ReadModel\Task") 
     * @ORM\JoinColumn(name="task_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $task;

    /** 
     * @ORM\Id 
     * @ORM\ManyToOne(targetEntity="Ora\User\User")
     * @ORM\JoinColumn(name="member_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $member;

    /**
     * @ORM\Column(type="string")
     * @var string
     */
    private $role;
    
    /** 
     * @ORM\Embedded(class="Ora\ReadModel\Estimation")
     * @var Estimation
     */
    private $estimation;
    
    /**
     * @ORM\OneToMany(targetEntity="Ora\ReadModel\Share", mappedBy="evaluator", cascade="persist", orphanRemoval=TRUE, indexBy="valued_id");
     * @var Share[]
     */
    private $shares;
    
    /**
     * @ORM\Column(type="decimal", precision=10, scale=4, nullable=true)
     * @var float
     */
	private $share;
	
    /**
     * @ORM\Column(type="decimal", precision=10, scale=4, nullable=true)
     * @var float
     */
	private $delta;
	
    /**
	 * @ORM\Column(type="datetime")
	 * @var DateTime
	 */
	protected $createdAt;
	
	/**
	 * @ORM\ManyToOne(targetEntity="Ora\User\User")
     * @ORM\JoinColumn(name="createdBy_id", referencedColumnName="id")
	 */
	protected $createdBy;
	
    /**
     * @ORM\Column(type="datetime")
     * @var datetime
     */
    protected $mostRecentEditAt;
    
    /**
     * @ORM\ManyToOne(targetEntity="Ora\User\User")
     * @ORM\JoinColumn(name="mostRecentEditBy_id", referencedColumnName="id")
     */
    protected $mostRecentEditBy;
    
	public function __construct(Task $task, User $member, $role){
        $this->task = $task;
        $this->member = $member;
        $this->role = $role;
        $this->shares = new ArrayCollection();
    }

    public function getRole() {
        return $this->role;
    }

    public function getMember() {
        return $this->member;
    }
    
    public function getTask() {
    	return $this->task;
    }
    
    public function getEstimation(){
    	return $this->estimation;
    }
    
    public function hasEstimated() {
    	return !is_null($this->estimation);
    }
    
    public function setEstimation(Estimation $estimation) {
    	$this->estimation = $estimation;
    	$this->mostRecentEditAt = $estimation->getCreatedAt();
    	$this->mostRecentEditBy = $this->member;
    	return $this;
    }

	public function getCreatedAt() {
		return $this->createdAt;
	}
	
	public function setCreatedAt(\DateTime $when) {
		$this->createdAt = $when;
		return $this->createdAt;
	}
	
    public function getCreatedBy() {
        return $this->createdBy;
    }
    
    public function setCreatedBy(User $user) {
    	$this->createdBy = $user;
    	return $this->createdBy;
    }

    public function getMostRecentEditAt() {
        return $this->mostRecentEditAt;
    }
    
	public function setMostRecentEditAt(\DateTime $when) {
		$this->mostRecentEditAt = $when;
		return $this->mostRecentEditAt;
	}
	
    public function getMostRecentEditBy() {
        return $this->mostRecentEditBy;
    }
    
    public function setMostRecentEditBy(User $user) {
    	$this->mostRecentEditBy = $user;
    	return $this->mostRecentEditBy;
    }
    
    public function assignShare(TaskMember $valued, $value, \DateTime $when) {
    	$share = new Share($this, $valued);
    	$share->setValue($value);
    	$share->setCreatedAt($when);
    	$this->shares->set($valued->getMember()->getId(), $share);
    	$this->task->updateMembersShare($when);
    	return $this;
    }
    
    public function getShareValueOf(TaskMember $valued) {
    	$s = $this->shares->get($valued->getMember()->getId());
    	return $s === null ? null : $s->getValue();
    }

    public function resetShares() {
    	$this->shares->clear();
    	return $this;
    }
    
    public function getShares() {
    	return $this->shares->toArray();
    }

    public function setShare($value, \DateTime $when) {
    	$this->share = $value;
    	$share = $this->shares->get($this->member->getId());
     	$this->delta = is_null($share) ? null : $value - $share->getValue();
     	$this->mostRecentEditAt = $when;
    	return $this;
    }
    
    public function getShare() {
    	return $this->share;
    }
    
    public function getDelta() {
    	return $this->delta;
    }
}
<?php
namespace Ora\ReadModel;

use Doctrine\ORM\Mapping AS ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Ora\User\User;
use Ora\ReadModel\Estimation;

/**
 * @ORM\Entity @ORM\Table(name="tasks_members")
 * @author Tilli Mario
 *
 */
class TaskMember {	

    /** 
     * @ORM\Id 
     * @ORM\ManyToOne(targetEntity="Ora\ReadModel\Task") 
     */
    private $task;

    /** 
     * @ORM\Id 
     * @ORM\ManyToOne(targetEntity="Ora\User\User") 
     */
    private $member;

    /**
     * @ORM\Column(type="string")
     * @var string
     */
    private $role;
    

    /** 
     * @ORM\OneToOne(targetEntity="Ora\ReadModel\Estimation")
     */
    private $estimation;
    
    


    public function __construct(Task $task, User $member, $role){

        $this->task = $task;
        $this->member = $member;
        $this->role = $role;
        
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
    
    public function setEstimation($estimation){
    	$this->estimation=$estimation;
    }

}

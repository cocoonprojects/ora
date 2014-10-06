<?php

namespace Ora\TaskManagement;

use Doctrine\ORM\Mapping AS ORM;
use Ora\DomainEntity;

/**
 * @ORM\Entity @ORM\Table(name="tasks")
 * @author Giannotti Fabio
 *
 */
class Task extends DomainEntity 
{	
    CONST STATUS_ONGOING = 1;
    
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
	
	//TODO: Da abilitare appena ci sarà qualche project
	///**
	//* @ManyToOne(targetEntity="Ora\ProjectManagement\Project")
	//*/
	private $project;	
	
	/**
	* @ORM\Column(type="datetime", nullable=TRUE)
	* @var datetime
	*/
	private $mostRecentEditAt;
	
	/**
	 * @ORM\ManyToOne(targetEntity="Ora\User\User")
	 */
	private $mostRecentEditBy;
	
	public function __construct($taskID, \DateTime $createdAt) 
	{
		parent::__construct($taskID, $createdAt);
		$this->status = self::STATUS_ONGOING;
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
	
	public function setProject($project) {
	    $this->project = $project;
	}
	
	public function getStatus() {
	    return $this->status;
	}
	
	public function setMostRecentEditAt($datetime) {
	    $this->mostRecentEditAt = $datetime;
	}
	
	public function getMostRecentEditAt() {
	    return $this->mostRecentEditAt;
	}
	
	public function setMostRecentEditBy($user) {
	    $this->mostRecentEditBy = $user;
	}
	
	public function getMostRecentEditBy() {
	    return $this->mostRecentEditBy;
	}
}
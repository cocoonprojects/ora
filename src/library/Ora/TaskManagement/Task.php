<?php

namespace Ora\TaskManagement;

use Doctrine\ORM\Mapping AS ORM;
use Ora\DomainEntity;

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
	
	// TODO: Utilizzare Ora\User\User $createdBy se createdBy dev'essere una relazione con lo USER
	public function __construct($taskID, \DateTime $createdAt, $createdBy) 
	{
		parent::__construct($taskID, $createdAt, $createdBy);
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
	
	// TODO: Definire come gestire il cambio stato sull'entità
	public function setStatus($status) {
	    $this->status = $status;
	}
	
	//TODO implement method when there will be members 
	public function isAcceptable(){
		return true;
	}
	
}
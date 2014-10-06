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
	
	//TODO: Abilitare non appena sarà pronta l'entità project
	///**
	//* @ManyToOne(targetEntity="Project")
	//*/
	private $project;	
	
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
}
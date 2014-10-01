<?php

namespace Ora\TaskManagement;

use Doctrine\ORM\Mapping AS ORM;
use Ora\DomainEntity;

/**
 * @ORM\Entity @ORM\Table(name="tasks")
 * @author Giannotti Fabio
 *
 */
class TaskEntity extends DomainEntity 
{	
	/**
	 * @ORM\Column(type="string")
	 * @var string
	 */
	private $description;
	
	//TODO: Abilitare non appena sarà pronta l'entità project
	///**
	//* @ManyToOne(targetEntity="ProjectEntity")
	//*/
	private $project;	
	
	public function __construct($taskID, $createdAt) 
	{
		parent::__construct($taskID, $createdAt);
	}
	
	public function getDescription() {
		return $this->description;
	}
	
	public function setDescription($description) {
		$this->description = $description;
	}
	
	public function getProject() {
	    return $this->project;
	}
	
	public function setProject($project) {
	    $this->project = $project;
	}
}
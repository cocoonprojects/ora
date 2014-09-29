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
	//* @ManyToOne(targetEntity="Core_Model_Entities_User")
	//*/
	private $project;	
	
	public function __construct($taskID, $createdAt, $eventStore) 
	{
		parent::__construct($taskID, $createdAt, $eventStore);
	}
	
	public function getDescription() 
	{
		return $this->description;
	}
	
	public function setDescription($description) 
	{
		$this->description = $description;
	}
	
	public function getProject()
	{
	    return $this->project;
	}
	
	public function setProject($project)
	{
	    $this->project = $project;
	}
}
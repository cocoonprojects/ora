<?php

namespace Ora\TaskManagement;

use Doctrine\ORM\Mapping AS ORM;
use Doctrine\Common\Collections\ArrayCollection AS ARRAYCOLLECTION;
use Ora\DomainEntity;

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
	
	/**
	 * @ORM\ManyToMany(targetEntity="Ora\User\User")
	 * @ORM\JoinTable(name="teams",
	 *      joinColumns={@ORM\JoinColumn(name="task_id", referencedColumnName="id")},
	 *      inverseJoinColumns={@ORM\JoinColumn(name="user_id", referencedColumnName="id")}
	 *      )
	 */
	private $members;
	
	// TODO: Utilizzare Ora\User\User $createdBy se createdBy dev'essere una relazione con lo USER
	public function __construct($taskID, \DateTime $createdAt, $createdBy) 
	{
		parent::__construct($taskID, $createdAt, $createdBy);
		$this->status = self::STATUS_ONGOING;
		$this->members = new ARRAYCOLLECTION();
	}
	
	public function getSubject() {
		return $this->subject;
	}
	
	public function setSubject($subject) {
		$this->subject = $subject;
	}
	
	public function getStatus() {
	    return $this->status;
	}
	
	// TODO: Definire come gestire il cambio stato sull'entità
	public function setStatus($status) {
	    $this->status = $status;
	}
	
	public function getProject() {
	    return $this->project;
	}
	
	public function setProject($project) {
	    $this->project = $project;
	}	
	
	public function removeMember($m) {
	    $this->members->removeElement($m);
	}
	
	public function addMember($m) {
	    $this->members[] = $m;
	}
	
	public function getMembers() {
	    return $this->members;
	}
	
	public function serializeToJSON($entitySerializer) 
	{
	    $serializedToArray = $this->serializeToARRAY($entitySerializer);
	    
	    return json_encode($serializedToArray); 
	}
	
	public function serializeToARRAY($entitySerializer)
	{
	    $serializedToArray = $entitySerializer->toArray($this);
        
	    // TODO: Controllare se il serializzatore di doctrine 
	    //       può recuperare automaticamente tali dati
	    $createdBy = $this->getCreatedBy();
	    $serializedToArray['created_by']['name'] = $createdBy->getName();
	    
	    //TODO: Serializzare i members
	    $members = $this->getMembers();
	    
	    $serializedToArray['members'] = array();
	    foreach ($members as $t)
	    {	        
            $serializedToArray['members'][] = $t->getName();
	    }
	    
	    return $serializedToArray;
	}
}
<?php

namespace Ora\ReadModel;

use Doctrine\ORM\Mapping AS ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity @ORM\Table(name="estimations")
 * @author Andrea Lupia
 */

class Estimation extends DomainEntity {

	CONST NOT_ESTIMATED = -1;
	
	/**
	 * @ORM\OneToOne(targetEntity="Ora\ReadModel\TaskMember", mappedBy="estimation")
	 */
	private $taskMember;
	
    /** @ORM\Column(type="decimal", precision=10, scale=2) */
    private $value;
    
    public function __construct($id, $value)
    {
    	$this->id = $id;
    	$this->value = $value;
    }
    
    public function getValue() {
    	return $this->value;
    }
    
    public function setValue($value) {
    	$this->value = $value;
    }
    
    public function getUser() {
    	return $this->getTaskMember()->getMember();
    }
    
    public function getTask() {
    	return $this->getTaskMember()->getTask();
    }
    
    public function getTaskMember(){
    	return $this->taskMember;
    }
    
}

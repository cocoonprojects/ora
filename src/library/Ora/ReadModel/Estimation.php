<?php

namespace Ora\ReadModel;

use Doctrine\ORM\Mapping AS ORM;
use Doctrine\Common\Collections\ArrayCollection;


//use Ora\DomainEntity;

/**
 * @ORM\Entity @ORM\Table(name="estimation")
 * @author Andrea Lupia
 */

class Estimation extends DomainEntity{

	/**
	 * @ORM\OneToOne(targetEntity="Ora\ReadModel\TaskMember", mappedBy="estimation")
	 */
	private $taskMember;
	
    /** @ORM\Column(type="decimal", precision=10, scale=2) */
    private $value;
    
    public function __construct(TaskMember $taskMember, $value)
    {
    	$this->taskMember= $taskMember;
    	$this->value = $value;
    }
    
    public function getValue() {
    	return $this->value;
    }
    
    public function setValue($value) {
    	$this->value = $value;
    }
    
    public function getTaskMember(){
    	return $this->taskMember;
    }
    
    public function setTaskMember($taskMember){
    	$this->taskMember=$taskMember;
    }
    
}

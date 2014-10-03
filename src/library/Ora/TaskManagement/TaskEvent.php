<?php

namespace Ora\TaskManagement;

use Doctrine\ORM\Mapping AS ORM;
use Ora\DomainEvent;
use \DateTime;
use Ora\EntitySerializer;

/**
* @ORM\Entity
* @author Giannotti Fabio
*/
class TaskEvent extends DomainEvent 
{
    private $entitySerializer;
    protected $task;
    
    protected function __construct(DateTime $firedAt, Task $task, EntitySerializer $entitySerializer) 
    {
        $this->entitySerializer = $entitySerializer;
        $this->task = $task;
        
        parent::__construct($firedAt);
    }
}
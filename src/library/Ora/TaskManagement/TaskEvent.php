<?php

namespace Ora\TaskManagement;

use Doctrine\ORM\Mapping AS ORM;
use Ora\DomainEvent;
use \DateTime;

/**
* @ORM\Entity
* @author Giannotti Fabio
*/
class TaskEvent extends DomainEvent 
{
    protected function __construct(DateTime $firedAt, TaskEntity $task) 
    {
        parent::__construct($firedAt);
        
        /*
        if ($task instanceof TaskEntity)
        {
            
        }
        else
            throw new Exception('Invalid Task Entity in '.get_class($this));
        */
    }
}
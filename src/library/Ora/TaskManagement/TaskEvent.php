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
    
    protected function __construct(DateTime $firedAt, TaskEntity $task, EntitySerializer $entitySerializer) 
    {
        $this->entitySerializer = $entitySerializer;
        
        /*
        if ($task instanceof TaskEntity)
        {
        */
            parent::__construct($firedAt);
            
            // Serialize TASK ENTITY to JSON
            $taskSerialized = $this->entitySerializer->toJson($task);
            // Save JSON serialized into event attributes
            $this->setAttributes($taskSerialized);
        /*}
        else
            throw new Exception('Invalid Task Entity in '.get_class($this));
        */
    }
}
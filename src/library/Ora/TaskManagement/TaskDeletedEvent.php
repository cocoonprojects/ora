<?php

namespace Ora\TaskManagement;

use Doctrine\ORM\Mapping AS ORM;
use \DateTime;
use Ora\EntitySerializer;

/**
* @ORM\Entity
* @author Giannotti Fabio
*/
final class TaskDeletedEvent extends TaskEvent 
{
    public function __construct(DateTime $firedAt, Task $task, EntitySerializer $entitySerializer) 
    {
        parent::__construct($firedAt, $task, $entitySerializer);
        
        //$serializedTask = $entitySerializer->toJson($task);
        $serializedTask = $task->serializeToJSON($entitySerializer);
        
        $this->attributes = $serializedTask;
    }
}
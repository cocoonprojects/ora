<?php

namespace Ora\TaskManagement;

use Doctrine\ORM\Mapping AS ORM;
use \DateTime;
use Ora\EntitySerializer;

/**
* @ORM\Entity
* @author Giannotti Fabio
*/
final class TaskCreated extends TaskEvent 
{
    public function __construct(DateTime $firedAt, TaskEntity $task, EntitySerializer $entitySerializer) 
    {
        parent::__construct($firedAt, $task, $entitySerializer);
    }
}
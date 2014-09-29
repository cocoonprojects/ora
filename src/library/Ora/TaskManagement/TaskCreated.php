<?php

namespace Ora\TaskManagement;

use Doctrine\ORM\Mapping AS ORM;
use \DateTime;

/**
* @ORM\Entity
* @author Giannotti Fabio
*/
final class TaskCreated extends TaskEvent 
{
    public function __construct(DateTime $firedAt, TaskEntity $task) 
    {
        parent::__construct($firedAt, $task);
    }
}
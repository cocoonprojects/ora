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
        /*
        if ($task instanceof TaskEntity)
        {
        */
            parent::__construct($firedAt);
            //TODO: SERIALIZZARE ENTITA' TASK ED INSERIRLA IN ATTRIBUTES
            //$this->setAttributes("{12}");
        /*}
        else
            throw new Exception('Invalid Task Entity in '.get_class($this));
        */
    }
}
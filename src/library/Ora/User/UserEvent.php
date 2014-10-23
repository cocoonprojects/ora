<?php

namespace Ora\User;

use Doctrine\ORM\Mapping AS ORM;
use Ora\DomainEvent;
use \DateTime;
use Ora\EntitySerializer;

/**
* @ORM\Entity
*/

class UserEvent extends DomainEvent 
{   
    protected function __construct(DateTime $firedAt, User $user, EntitySerializer $entitySerializer) 
    {
        parent::__construct($firedAt);
    }
}
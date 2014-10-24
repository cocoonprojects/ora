<?php

namespace Ora\Organization;

use Doctrine\ORM\Mapping AS ORM;
use Ora\DomainEvent;
use \DateTime;
use Ora\EntitySerializer;

/**
* @ORM\Entity
*/

class OrganizationEvent extends DomainEvent 
{   
    protected function __construct(DateTime $firedAt, Organization $organization, EntitySerializer $entitySerializer) 
    {
        parent::__construct($firedAt);
    }
}
<?php

namespace Ora\Organization;

use Doctrine\ORM\Mapping AS ORM;
use Ora\DomainEvent;
use \DateTime;
use Ora\EntitySerializer;
use Ora\UserOrganization\UserOrganization;
/**
* @ORM\Entity
*/

class UserOrganizationEvent extends DomainEvent 
{   
    protected function __construct(DateTime $firedAt, UserOrganization $userOrganization, EntitySerializer $entitySerializer) 
    {
        parent::__construct($firedAt);
    }
}
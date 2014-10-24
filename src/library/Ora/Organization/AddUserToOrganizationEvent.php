<?php

namespace Ora\Organization;

use Doctrine\ORM\Mapping AS ORM;
use \DateTime;
use Ora\EntitySerializer;
use Ora\UserOrganization\UserOrganization;

/**
* @ORM\Entity
*/
final class AddUserToOrganizationEvent extends UserOrganizationEvent 
{
    public function __construct(DateTime $firedAt, UserOrganization $userOrganization, EntitySerializer $entitySerializer) 
    {
        parent::__construct($firedAt, $userOrganization, $entitySerializer);
        
        $serialized = $userOrganization->serializeToJSON($entitySerializer);
        
        $this->attributes = $serialized;
    }
}
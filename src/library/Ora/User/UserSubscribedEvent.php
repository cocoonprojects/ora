<?php

namespace Ora\User;

use Doctrine\ORM\Mapping AS ORM;
use \DateTime;
use Ora\EntitySerializer;

/**
* @ORM\Entity
*/
final class UserSubscribedEvent extends UserEvent 
{
    public function __construct(DateTime $firedAt, User $user, EntitySerializer $entitySerializer) 
    {
        parent::__construct($firedAt, $user, $entitySerializer);
        
        $serialized = $user->serializeToJSON($entitySerializer);
        
        $this->attributes = $serialized;
    }
}

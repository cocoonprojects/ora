<?php

namespace Application\Service;

use BjyAuthorize\Provider\Identity\ProviderInterface;
use Zend\Authentication\AuthenticationService;
use Doctrine\ORM\EntityManager;
use Ora\User\User;

class IdentityRolesProvider implements ProviderInterface
{
    
   /**
    * @var AuthenticationService
    */
    private $authService;

    private $defaultRole = User::ROLE_GUEST;

    public function __construct( AuthenticationService $authService){
        $this->authService = $authService;
    }
    
    public function getIdentityRoles(){

        if ($this->authService->hasIdentity()) {

            $identity = $this->authService->getIdentity()['user'];
            //recupero il ruolo dall'utente autenticato
            return $identity->getRoles();
        }
        
        return $this->getDefaultRole();
    }

    public function getDefaultRole(){
        return $this->defaultRole;
    }

}











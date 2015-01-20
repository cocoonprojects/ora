<?php
namespace ZendExtension\Mvc\Controller\Plugin;

use Ora\User\UserService;
use Zend\Authentication\AuthenticationServiceInterface;
use Zend\Mvc\Controller\Plugin\AbstractPlugin;

/**
 * Controller plugin to fetch the authenticated identity.
 */
class LoggedIdentity extends AbstractPlugin
{
    /**
     * @var AuthenticationServiceInterface
     */
    protected $authenticationService;
    /**
     * 
     * @var UserService
     */
    protected $userService;
    
    protected $user;

    public function __construct(AuthenticationServiceInterface $authenticationService, UserService $userService) {
    	$this->authenticationService = $authenticationService;
    	$this->userService = $userService;
    }
    /**
     * @return AuthenticationService
     */
    public function getAuthenticationService()
    {
        return $this->authenticationService;
    }

    /**
     * Retrieve the current identity, if any.
     *
     * If none is present, returns null.
     *
     * @return mixed|null
     * @throws Exception\RuntimeException
     */
    public function __invoke()
    {
        if (!$this->authenticationService->hasIdentity()) {
            return null;
        }
        $u = $this->authenticationService->getIdentity()['user'];
        if(is_null($this->user) || !$this->user->equals($u)) {
	        $id = $u->getId();
	        $this->user = $this->userService->findUser($id);
        }
        return $this->user;
    }
}

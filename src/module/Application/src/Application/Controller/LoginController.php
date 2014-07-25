<?php
/**
 * Carmati CRM
 *
 */

namespace Application\Controller;

use Zend\Mvc\Controller\AbstractRestfulController;
use Zend\View\Model\JsonModel;

class LoginController extends AbstractRestfulController
{
    private $loginService;

    /**
     * Class constructor
     * 
     * @param \Application\Service\LoginService $loginService
     */
    public function __construct($loginService) 
    {
        $this->loginService = $loginService;
    }

    
}

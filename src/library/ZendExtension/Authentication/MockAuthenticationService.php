<?php
namespace ZendExtension\Authentication;

use Zend\Authentication\AuthenticationServiceInterface;
use Application\Entity\User;
use Zend\Authentication\Result;
use Zend\Authentication\AuthenticationService;

/**
 * This Mock should estend AuthenticationServiceInterface, but due to a bug of ZF2 in identity controller plugin it must exted AuthenticationService
 * @author andreabandera
 *
 */
class MockAuthenticationService extends AuthenticationService {
	
	private $identity = array();
	
	public function __construct(User $user = null) {
		$this->identity['user'] = $user;	
	}
	
	public function authenticate() {
		return empty($this->identity) ? Result::FAILURE : Result::SUCCESS;
	}
	
	public function getIdentity() {
		return $this->identity;
	}
	
	public function hasIdentity() {
		return empty($this->identity) ? false : true;
	}
	
	public function clearIdentity() {
		$this->identity = null;
	}
}
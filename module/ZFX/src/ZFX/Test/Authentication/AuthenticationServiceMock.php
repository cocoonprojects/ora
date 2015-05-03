<?php
namespace ZFX\Authentication;

use Zend\Authentication\AuthenticationServiceInterface;
use Application\Entity\User;
use Zend\Authentication\Result;
use Zend\Authentication\AuthenticationService;

/**
 * This Mock should estend AuthenticationServiceInterface, but due to a bug of ZF2 in identity controller plugin it must extend AuthenticationService
 * @author andreabandera
 *
 */
class AuthenticationServiceMock extends AuthenticationService {
	
	private $identity = null;
	
	public function __construct(User $user = null) {
		$this->setIdentity($user);
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
	
	public function setIdentity(User $user = null) {
		$this->identity = $user == null ? null : ['user' => $user];
	}
}
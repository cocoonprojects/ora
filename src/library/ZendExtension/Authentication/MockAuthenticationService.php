<?php
namespace ZendExtension\Authentication;

use Zend\Authentication\AuthenticationServiceInterface;
use Ora\User\User;
use Zend\Authentication\Result;

class MockAuthenticationService implements AuthenticationServiceInterface {
	
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
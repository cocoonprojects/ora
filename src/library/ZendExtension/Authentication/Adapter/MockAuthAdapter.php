<?php
namespace ZendExtension\Authentication\Adapter;

use Zend\Authentication\Adapter\AdapterInterface;
use Ora\User\UserService;
use Zend\Authentication\Result;

class MockAuthAdapter implements AdapterInterface
{
	/**
	 * 
	 * @var string
	 */
	private $email;
	
	/**
	 * 
	 * @var UserService
	 */
	private $service;
	
	public function __construct(UserService $service) {
		$this->service = $service;
	}
	
	public function authenticate() {
		$user = $this->service->findUserByEmail($this->email);
		if(is_null($user)) {
			return new Result(Result::FAILURE_IDENTITY_NOT_FOUND, $this->email);
		}
		return new Result(Result::SUCCESS, array('user' => $user));
	}
	
	public function setEmail($email) {
		$this->email = $email;
	}
}
<?php
namespace Application\Authentication\OAuth2;

use Zend\Authentication\Result;
use Zend\Mvc\Controller\AbstractController;
use Application\Authentication\AdapterResolver;
use Application\Service\UserService;
use ZFX\Test\Authentication\OAuth2AdapterMock;

class UserServiceOAuth2AdapterMock extends OAuth2AdapterMock implements AdapterResolver
{
	/**
	 * 
	 * @var UserService
	 */
	private $service;

	public function __construct(UserService $userService) {
		$this->service = $userService;
	}
	
	public function getAdapter(AbstractController $controller) {
		$email = $controller->getRequest()->getPost('email');
		if(empty($email)) {
			throw new InvalidTokenException('email post parameter cannot be null');
		}
		$this->setEmail($email);
		return $this;
	}
	
	public function authenticate() {
		$email = $this->getEmail();

		$user = $this->service->findUserByEmail($email);
		if(is_null($user)) {
			$offset = strlen($email) - strlen('@ora.local');
			if(strpos($email, '@ora.local', $offset) === FALSE) {
				return new Result(Result::FAILURE_IDENTITY_NOT_FOUND, $email);
			} else {
				$this->setFirstname('Fred');
				$this->setLastname('Buscaglione');
			}
		} else {
			$this->setEmail($user->getEmail());
			$this->setFirstname($user->getFirstname());
			$this->setLastname($user->getLastname());
		}
		return parent::authenticate();
	}
	
	public function getProviders() {
		return [];
	}
}

<?php

namespace Application\Controller;

use Application\Authentication\JWTUtils;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\JsonModel;
use Zend\View\Model\ViewModel;
use Zend\Authentication\AuthenticationServiceInterface;
use Zend\Session\Container;
use Application\Authentication\AdapterResolver;
use Application\Authentication\OAuth2\InvalidTokenException;

class AuthController extends AbstractActionController
{
	/**
	 * 
	 * @var AdapterResolver
	 */
	private $adapterResolver;
	/**
	 * 
	 * @var AuthenticationServiceInterface
	 */
	private $authService;

	public function __construct(AuthenticationServiceInterface $authService, AdapterResolver $adapterResolver) {
		$this->authService = $authService;
		$this->adapterResolver = $adapterResolver;
	}
		
	public function loginAction()
	{
		$json = new JsonModel();
		try {
			$adapter = $this->adapterResolver->getAdapter($this);
			if(is_null($adapter)) {
				$json->setVariable('error', 'Auth.InvalidProvider');
			} else {
				$result = $this->authService->authenticate($adapter);
				if($result->isValid()) {
					return $json->setVariable('token', JWTUtils::buildJWT($result->getIdentity()));
				}
			}
		} catch (InvalidTokenException $e) {
			return $json->setVariable('error', $e->getMessage());
		}
		return $json;
	}
	
	public function logoutAction()
	{
		$this->authService->clearIdentity();
		$sm = Container::getDefaultManager();
		$sm->destroy();
		return $this->redirect()->toRoute('home');
	}
}
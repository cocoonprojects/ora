<?php

namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
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
		$view = new ViewModel();
		try {
			$adapter = $this->adapterResolver->getAdapter($this);
			if(is_null($adapter)) {
				$view->setVariable('error', 'Auth.InvalidProvider');
			} else {
				$result = $this->authService->authenticate($adapter);
				
				if(getenv('APPLICATION_ENV') != 'acceptance') {
					if($result->isValid()) {
						$this->redirect()->toRoute('home');
					}
				}
				$view->setVariable('authenticate', $result);
			}
		} catch (InvalidTokenException $e) {
			$view->setVariable('error', $e->getMessage());
		}
		
		return $view;
	}
	
	public function logoutAction()
	{
		$this->authService->clearIdentity();
		$sm = Container::getDefaultManager();
		$sm->destroy();
		return $this->redirect()->toRoute('home');
	}
}
<?php

namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Authentication\Result;
use Zend\Authentication\AuthenticationServiceInterface;
use ZendExtension\Authentication\AdapterResolver;
use ZendExtension\Authentication\OAuth2\InvalidTokenException;
use Ora\User\UserService;
use Zend\Session\Container;

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
	/**
	 * 
	 * @var UserService
	 */
	private $userService;
	
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
				$adapter->getEventManager()->attach('oauth2.success', array($this, 'loadUser'));
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
	
	public function loadUser($e)
	{
		$args = $e->getParams();
		$info = $args['info'];
		
		//var_dump($args['info']);echo"---------------------------------------------";
		
		
		switch($args['provider'])
		{
			case 'linkedin':
				$info['email'] = $info['emailAddress'];
				$info['given_name'] = $info['firstName'];
				$info['family_name'] = $info['lastName'];
				$info['picture'] = $info['pictureUrl'];
				break;
		}

		$user = $this->userService->findUserByEmail($info['email']);
		if(is_null($user))
		{
			$user = $this->userService->subscribeUser($info);
		}

		$args['info']['user'] = $user;
		$args['info']['provider'] = $args['provider'];
		
		//echo"---------------------------------------------";echo sizeof($args['info']['user']->getOrganizationMemberships());echo"---------------------------------------------";
		
		
	}	
	
	public function setUserService(UserService $userService) {
		$this->userService = $userService;
	}
}
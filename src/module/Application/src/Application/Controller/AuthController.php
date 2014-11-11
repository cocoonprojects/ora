<?php

namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Ora\Organization\Organization;
use Zend\Authentication\AuthenticationService;
use ZendExtension\Authentication\Adapter\MockAuthAdapter;
use Zend\Authentication\Result;

class AuthController extends AbstractActionController
{        
	private $providers;
	private $authService;
	private $redirectAfterLogout;
	
	public function __construct(AuthenticationService $authService, array $providers) {
		$this->authService = $authService;
		$this->providers = $providers;
	}
		
	public function loginAction()
	{
		$view = new ViewModel();
		if(strlen($this->params('code')) > 10)
		{
			//TODO: $this->getServiceLocator()->get('Zend\Log\Logger')->crit('Auth: Error code parameter: '.$this->getRequest()->getQuery('code'));
			$view->setVariable('error', 'Auth.InvalidCode');
			return $view;
		}
	
		$id = $this->params('id');
		if(empty($id) || !array_key_exists($id, $this->providers))
		{
			//TODO: $this->getServiceLocator()->get('Zend\Log\Logger')->crit('Auth: Error Provider parameter: '.$provider);
			$view->setVariable('error', 'Auth.InvalidProvider');
			return $view;
		}
		 
		$provider = $this->providers[$id];
		if(is_null($provider)) {
			//TODO: $this->getServiceLocator()->get('Zend\Log\Logger')->crit('Auth: Error Provider class missing: '.$provider);
			$view->setVariable('error', 'Auth.InvalidProvider');
			return $view;
		}
		if(!$provider->getToken($this->request))
		{
			//TODO: $this->getServiceLocator()->get('Zend\Log\Logger')->crit('Auth: InvalidToken');
			$view->setVariable('error', 'Auth.InvalidToken');
			return $view;
		}
	
		$adapter = $this->serviceLocator->get('ZendOAuth2\Auth\Adapter');
		$adapter->setOAuth2Client($provider);
		$adapter->getEventManager()->attach('oauth2.success', array($this, 'loadUser'));
		$result = $this->authService->authenticate($adapter);
		$view->setVariable('authenticate', $result);
	
		return $view;
	}
	
	public function logoutAction()
	{
		if(!$this->authService->hasIdentity())
		{
			$this->redirect()->toRoute('home');
			return;
		}
		 
		$identity = $this->authService->getIdentity();
	
		$this->authService->clearIdentity();
		 
		if(array_key_exists('sessionOfProvider', $identity) &&
				"" != $identity["sessionOfProvider"])
		{
			$identity["sessionOfProvider"]->clear();
			 
		}
			
		return $this->redirect()->toRoute('home');
	}
	
	public function loadUser($e)
	{
		$userService = $this->serviceLocator->get('User\UserService');
		$args = $e->getParams();
		
		$info = $args['info'];
		switch($args['provider'])
		{
			case 'linkedin':
				$info['email'] = $info['emailAddress'];
				$info['given_name'] = $info['firstName'];
				$info['family_name'] = $info['lastName'];
				$info['picture'] = $info['pictureUrl'];
				break;
		}

		$user = $userService->findUserByEmail($info['email']);
		if(is_null($user))
		{
			$user = $userService->subscribeUser($info);
		}

		$args['info']['user'] = $user;
		$args['info']['provider'] = $args['provider'];
	}	
	
	public function acceptanceLoginAction() {
// 		$env = getenv('APPLICATION_ENV') ? : "local";
// 		if($env = 'acceptance') {
		$adapter = new MockAuthAdapter();
		$email = $this->params()->fromPost('email');
		$adapter->setEmail($email);
		$userService = $this->serviceLocator->get('User\UserService');
		$adapter->setUserService($userService);
		$result = $this->authService->authenticate($adapter);

		if($this->authService->hasIdentity())
		{
			$this->response->setStatusCode(200);
		}
		else
		{
			$this->response->setStatusCode(505);
		}
		
		/*if($result->getCode() == Result::FAILURE_IDENTITY_NOT_FOUND) {
			$this->response->setStatusCode(400);
		} else {
			$this->response->setStatusCode(200);
		}*/
// 		} else {
//			$this->response->setStatusCode(404);
//		}
		return $this->response;
	}
}
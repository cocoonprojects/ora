<?php
namespace Application\Authentication\OAuth2;

use Zend\Authentication\Result;
use Zend\Authentication\Adapter\AdapterInterface;
use Zend\EventManager\EventManagerAwareInterface;
use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\EventManager;
use Zend\Mvc\Controller\AbstractController;
use ZendOAuth2\AbstractOAuth2Client;
use Application\Authentication\AdapterResolver;
use Application\Service\UserService;

class MockOAuth2Adapter implements AdapterInterface, EventManagerAwareInterface, AdapterResolver
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
	/**
	 * 
	 * @var EventManagerInterface
	 */
	private $events;
		
	public function __construct(UserService $userService) {
		$this->service = $userService;
	}
	
	public function getAdapter(AbstractController $controller) {
		$this->email = $controller->getRequest()->getPost('email');
		if(empty($this->email)) {
			throw new InvalidTokenException('email post parameter cannot be null');
		}
		return $this;
	}
	
	public function authenticate() {
		$user = $this->service->findUserByEmail($this->email);
		if(is_null($user)) {
			$offset = strlen($this->email) - strlen('@ora.local');
			if(strpos($this->email, '@ora.local', $offset) === FALSE) {
				return new Result(Result::FAILURE_IDENTITY_NOT_FOUND, $this->email);
			} else {
				$args['info']['email'] = $this->email;
				$args['info']['given_name'] = 'Fred';
				$args['info']['family_name'] = 'Buscaglione';
			}
		} else {
			$args['info']['email'] = $user->getEmail();
			$args['info']['given_name'] = $user->getFirstname();
			$args['info']['family_name'] = $user->getLastname();
		}
		$args['info']['picture'] = 'fake';
		$args['provider'] = 'mock';
		
		$args = $this->getEventManager()->prepareArgs($args);
		$this->getEventManager()->trigger('oauth2.success', $this, $args);
		
		$rv = new Result(Result::SUCCESS, $args['info']);
		return $rv;
	}
	
	public function setEmail($email) {
		$this->email = $email;
	}
	
    public function setEventManager(EventManagerInterface $events) {
        $events->setIdentifiers(__CLASS__);
        $this->events = $events;
        return $this;
    }
    
    public function getEventManager() {
        if (null === $this->events) {
            $this->setEventManager(new EventManager());
        }
    	return $this->events;
    }
    
    public function getProviders() {
    	return [];
    }
	
}
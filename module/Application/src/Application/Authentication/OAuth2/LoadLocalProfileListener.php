<?php

namespace Application\Authentication\OAuth2;


use Application\Service\UserService;
use Zend\Authentication\Result;
use Zend\EventManager\Event;
use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\ListenerAggregateInterface;
use ZFX\Authentication\GoogleJWTAdapter;
use ZFX\Authentication\JWTAdapter;

class LoadLocalProfileListener implements ListenerAggregateInterface {

	protected $listeners = array();
	/**
	 *
	 * @var UserService
	 */
	private $userService;
	/**
	 * @var \Google_Client
	 */
	private $google;

	public function __construct(UserService $userService, \Google_Client $google) {
		$this->userService = $userService;
		$this->google = $google;
	}

	/**
	 * Attach one or more listeners
	 *
	 * Implementors may add an optional $priority argument; the EventManager
	 * implementation will pass this to the aggregate.
	 *
	 * @param EventManagerInterface $events
	 *
	 * @return void
	 */
	public function attach(EventManagerInterface $events)
	{
		$this->listeners[] = $events->getSharedManager()->attach(JWTAdapter::class, 'jwt.success', array($this, 'loadJWTUser'));
		$this->listeners[] = $events->getSharedManager()->attach(GoogleJWTAdapter::class, 'google-jwt.success', array($this, 'loadGoogleJWTUser'));
	}

	/**
	 * Detach all previously attached listeners
	 *
	 * @param EventManagerInterface $events
	 *
	 * @return void
	 */
	public function detach(EventManagerInterface $events)
	{
		if($events->getSharedManager()->detach('ZendOAuth2\Authentication\Adapter\ZendOAuth2', $this->listeners[0])) {
			unset($this->listeners[0]);
		}
	}

	public function loadJWTUser(Event $event)
	{
		$args = $event->getParams();
		$info = $args['info'];

		$user = $this->userService->findUser($info['uid']);
		if(is_null($user)) {
			$args['code'] = Result::FAILURE_IDENTITY_NOT_FOUND;
			$args['info'] = null;
		}

		$args['info'] = $user;
	}

	public function loadGoogleJWTUser(Event $event)
	{
		$args = $event->getParams();
		$info = $args['info'];

		$user = $this->userService->findUserByGoogleId($info['sub']);
		if(is_null($user)) {
			$user = $this->userService->subscribeUser($info);
		}

		$args['info'] = $user;
	}
}

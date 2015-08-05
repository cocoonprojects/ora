<?php

namespace Application\Authentication\OAuth2;


use Application\Service\UserService;
use Zend\Authentication\Result;
use Zend\EventManager\Event;
use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\ListenerAggregateInterface;
use ZFX\Authentication\JWTAdapter;

class LoadLocalProfileListener implements ListenerAggregateInterface {

	protected $listeners = array();
	/**
	 *
	 * @var UserService
	 */
	private $userService;

	public function __construct(UserService $userService) {
		$this->userService = $userService;
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
		$this->listeners[] = $events->getSharedManager()->attach('ZendOAuth2\Authentication\Adapter\ZendOAuth2', 'oauth2.success', array($this, 'loadUser'));
		$this->listeners[] = $events->getSharedManager()->attach(JWTAdapter::class, 'jwt.success', array($this, 'loadJWTUser'));
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

	public function loadUser(Event $event)
	{
		$args = $event->getParams();
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

		$user = $this->userService->findUserByEmail($info['email']);
		if(is_null($user)) {
			$user = $this->userService->subscribeUser($info);
		}

		$args['info'] = $user;
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
}

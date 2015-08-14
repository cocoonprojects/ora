<?php

namespace ZFX\Authentication;


use Zend\Authentication\Adapter\AdapterInterface;
use Zend\Authentication\Result;
use Zend\EventManager\EventManager;
use Zend\EventManager\EventManagerAwareInterface;
use Zend\EventManager\EventManagerInterface;

class GoogleJWTAdapter implements AdapterInterface, EventManagerAwareInterface
{
	/**
	 * @var string
	 */
	private $token;
	/**
	 * @var \Google_Client
	 */
	private $client;
	/**
	 * @var EventManagerInterface
	 */
	protected $eventManager;

	/**
	 * GoogleSignInAdapter constructor.
	 * @param \Google_Client $client
	 */
	public function __construct(\Google_Client $client)
	{
		$this->client = $client;
	}

	/**
	 * @param string $token
	 */
	public function setToken($token)
	{
		$this->token = $token;
	}

	/**
	 * Performs an authentication attempt
	 *
	 * @return \Zend\Authentication\Result
	 * @throws \Zend\Authentication\Adapter\Exception\ExceptionInterface If authentication cannot be performed
	 */
	public function authenticate()
	{
		try {
			$ticket = $this->client->verifyIdToken($this->token);
			if($ticket) {
				$data = $ticket->getAttributes();
				$args['code']     = Result::SUCCESS;
				$args['info']     = $data['payload'];
				$args['token']    = $this->token;
				$args['provider'] = 'google';

				$args = $this->getEventManager()->prepareArgs($args);
				$this->getEventManager()->trigger('google-jwt.success', $this, $args);
				return new Result($args['code'], $args['info']);
			}
			return new Result(Result::FAILURE, null);
		} catch (\Google_Auth_Exception $e) {
			return new Result(Result::FAILURE_CREDENTIAL_INVALID, null, [$e->getMessage()]); // Expired token or broken sign
		}
	}

	/**
	 * Inject an EventManager instance
	 *
	 * @param  EventManagerInterface $eventManager
	 * @return void
	 */
	public function setEventManager(EventManagerInterface $eventManager)
	{
		$eventManager->setIdentifiers(array(
			__CLASS__,
			get_called_class(),
		));
		$this->eventManager = $eventManager;
		return $this;
	}

	/**
	 * Retrieve the event manager
	 *
	 * Lazy-loads an EventManager instance if none registered.
	 *
	 * @return EventManagerInterface
	 */
	public function getEventManager()
	{
		if (null === $this->eventManager) {
			$this->setEventManager(new EventManager());
		}
		return $this->eventManager;
	}

}
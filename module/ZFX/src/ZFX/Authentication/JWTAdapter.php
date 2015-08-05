<?php

namespace ZFX\Authentication;


use Zend\Authentication\Adapter\AdapterInterface;
use Zend\Authentication\Result;
use Zend\EventManager\EventManager;
use Zend\EventManager\EventManagerAwareInterface;
use Zend\EventManager\EventManagerInterface;

class JWTAdapter implements AdapterInterface, EventManagerAwareInterface
{
	private $token;
	/**
	 * @var JWTBuilder
	 */
	private $parser;
	/**
	 * @var EventManager
	 */
	protected $eventManager;

	/**
	 * JWTAdapter constructor.
	 * @param JWTBuilder $parser
	 */
	public function __construct(JWTBuilder $parser)
	{
		$this->parser = $parser;
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
		$payload = $this->parser->parsePayload($this->token);
		if(is_null($payload)) {
			return new Result(Result::FAILURE_CREDENTIAL_INVALID, null); // Expired token or broken sign
		}
		$args['code']     = Result::SUCCESS;
		$args['info']     = $payload;
		$args['token']    = $this->token;
		$args['provider'] = 'jwt';

		$args = $this->getEventManager()->prepareArgs($args);
		$this->getEventManager()->trigger('jwt.success', $this, $args);

		return new Result($args['code'], $args['info']);
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
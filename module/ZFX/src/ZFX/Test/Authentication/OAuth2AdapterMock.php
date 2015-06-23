<?php
/**
 * Created by PhpStorm.
 * User: andreabandera
 * Date: 23/06/15
 * Time: 11:03
 */

namespace ZFX\Test\Authentication;


use Zend\Authentication\Adapter\AdapterInterface;
use Zend\Authentication\Result;
use Zend\EventManager\EventManager;
use Zend\EventManager\EventManagerAwareInterface;
use Zend\EventManager\EventManagerInterface;

class OAuth2AdapterMock implements AdapterInterface, EventManagerAwareInterface
{
	/**
	 *
	 * @var EventManagerInterface
	 */
	protected $events;

	protected $user = [];

	public function setEmail($email)
	{
		$this->user['email'] = $email;
	}

	public function getEmail()
	{
		if(isset($this->user['email'])) {
			return $this->user['email'];
		}
		return null;
	}

	public function setFirstname($firstname)
	{
		$this->user['given_name'] = $firstname;
		return $this;
	}

	public function setLastname($lastname)
	{
		$this->user['family_name'] = $lastname;
		return $this;
	}

	/**
	 * Performs an authentication attempt
	 *
	 * @return \Zend\Authentication\Result
	 * @throws \Zend\Authentication\Adapter\Exception\ExceptionInterface If authentication cannot be performed
	 */
	public function authenticate()
	{
		$this->user['picture'] = 'http://lorempixel.com/400/400/people/';
		$args = $this->getEventManager()->prepareArgs([
			'info' => $this->user,
			'provider' => 'mock'
		]);
		$this->getEventManager()->trigger('oauth2.success', $this, $args);
		$rv = new Result(Result::SUCCESS, $args['info']);
		return $rv;
	}

	/**
	 * Inject an EventManager instance
	 *
	 * @param  EventManagerInterface $eventManager
	 * @return void
	 */
	public function setEventManager(EventManagerInterface $events)
	{
		$events->setIdentifiers('ZendOAuth2\Authentication\Adapter\ZendOAuth2');
		$this->events = $events;
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
		if (null === $this->events) {
			$this->setEventManager(new EventManager());
		}
		return $this->events;
	}
}
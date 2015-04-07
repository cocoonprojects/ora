<?php
namespace Accounting\Service;

use Zend\EventManager\ListenerAggregateInterface;
use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\Event;
use Accounting\Service\AccountService;
use Ora\User\User;

class CreatePersonalAccountListener implements ListenerAggregateInterface {
	
	protected $listeners = array();
	
	/**
	 * 
	 * @var AccountService
	 */
	protected $accountService;
	
	public function __construct(AccountService $accountService) {
		$this->accountService = $accountService;
	}
	
	public function attach(EventManagerInterface $events) {
		$accountService = $this->accountService;
		$this->listeners[] = $events->getSharedManager()->attach('Application\UserService', User::EVENT_CREATED, function(Event $event) use ($accountService) {
			$user = $event->getTarget();
			$this->accountService->createPersonalAccount($user);
		});
		$this->events = $events;
	}
	
    public function detach(EventManagerInterface $events)
    {
    	if($events->getSharedManager()->detach('Application\UserService', $this->listeners[0])) {
    		unset($this->listeners[0]);
    	}
    }
}
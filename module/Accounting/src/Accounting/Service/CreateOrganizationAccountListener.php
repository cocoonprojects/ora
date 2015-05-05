<?php
namespace Accounting\Service;

use Zend\EventManager\ListenerAggregateInterface;
use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\Event;
use People\Organization;
use Accounting\Service\AccountService;

class CreateOrganizationAccountListener implements ListenerAggregateInterface {
	
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
		$this->listeners[] = $events->getSharedManager()->attach('People\OrganizationService', Organization::EVENT_CREATED, function(Event $event) use ($accountService) {
			$organization = $event->getTarget();
			$holder = $event->getParam('by');
			$accountService->createOrganizationAccount($organization, $holder);
		});
		$this->events = $events;
	}
	
    public function detach(EventManagerInterface $events)
    {
        if($events->getSharedManager()->detach('People\OrganizationService', $this->listeners[0])) {
    		unset($this->listeners[0]);
    	}
    }
}
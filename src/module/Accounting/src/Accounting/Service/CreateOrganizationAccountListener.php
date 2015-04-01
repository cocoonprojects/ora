<?php
namespace Accounting\Service;

use Zend\EventManager\ListenerAggregateInterface;
use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\Event;
use Ora\Accounting\AccountService;
use Application\Organization;

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
		$this->listeners[] = $events->getSharedManager()->attach('Application\OrganizationService', Organization::EVENT_CREATED, function(Event $event) use ($accountService) {
			$organization = $event->getTarget();
			$holder = $event->getParam('by');
			$accountService->createOrganizationAccount($organization, $holder);
		});
		$this->events = $events;
	}
	
    public function detach(EventManagerInterface $events)
    {
        foreach ($this->listeners as $index => $listener) {
            if ($events->detach($listener)) {
                unset($this->listeners[$index]);
            }
        }
    }
}
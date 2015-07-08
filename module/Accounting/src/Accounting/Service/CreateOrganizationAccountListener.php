<?php
namespace Accounting\Service;

use Application\Service\UserService;
use People\OrganizationCreated;
use People\Service\OrganizationService;
use Zend\EventManager\ListenerAggregateInterface;
use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\Event;
use Zend\Mvc\Application;

class CreateOrganizationAccountListener implements ListenerAggregateInterface
{
	protected $listeners = array();
	/**
	 * @var AccountService
	 */
	protected $accountService;
	/**
	 * @var OrganizationService
	 */
	protected $organizationService;
	/**
	 * @var UserService
	 */
	protected $userService;

	public function __construct(AccountService $accountService, OrganizationService $organizationService, UserService $userService) {
		$this->accountService = $accountService;
		$this->organizationService = $organizationService;
		$this->userService = $userService;
	}
	
	public function attach(EventManagerInterface $events) {
		$this->listeners[] = $events->getSharedManager()->attach(Application::class, OrganizationCreated::class, function(Event $event) {
			$streamEvent = $event->getTarget();
			$organizationId = $streamEvent->metadata()['aggregate_id'];
			$organization = $this->organizationService->getOrganization($organizationId);
			$holderId = $event->getParam('by');
			$holder = $this->userService->findUser($holderId);
			$this->accountService->createOrganizationAccount($organization, $holder);
		});
	}
	
	public function detach(EventManagerInterface $events)
	{
		if($events->getSharedManager()->detach(Application::class, $this->listeners[0])) {
			unset($this->listeners[0]);
		}
	}
}
<?php
namespace Accounting\Service;

use Application\Service\UserService;
use People\OrganizationMemberAdded;
use People\Service\OrganizationService;
use Zend\EventManager\ListenerAggregateInterface;
use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\Event;
use Zend\Mvc\Application;

class CreatePersonalAccountListener implements ListenerAggregateInterface
{
	protected $listeners = array();
	/**
	 * @var AccountService
	 */
	protected $accountService;
	/**
	 * @var UserService
	 */
	protected $userService;
	/**
	 * @var OrganizationService
	 */
	protected $organizationService;

	public function __construct(AccountService $accountService, UserService $userService, OrganizationService $organizationService) {
		$this->accountService = $accountService;
		$this->userService    = $userService;
		$this->organizationService = $organizationService;
	}
	
	public function attach(EventManagerInterface $events) {
		$this->listeners[] = $events->getSharedManager()->attach(Application::class, OrganizationMemberAdded::class, function(Event $event) {
			$streamEvent = $event->getTarget();
			$organizationId = $streamEvent->metadata()['aggregate_id'];
			$organization = $this->organizationService->getOrganization($organizationId);
			$userId = $event->getParam ( 'userId' );
			$user = $this->userService->findUser($userId);
			$this->accountService->createPersonalAccount($user, $organization);
		});
	}
	
	public function detach(EventManagerInterface $events)
	{
		if($events->getSharedManager()->detach(Application::class, $this->listeners[0])) {
			unset($this->listeners[0]);
		}
	}
}
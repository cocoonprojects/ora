<?php

namespace Kanbanize\Service;

use People\Service\OrganizationService;
use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\Event;
use Zend\EventManager\ListenerAggregateInterface;

class ImportTasksListener implements ListenerAggregateInterface{
	
	/**
	 * @var OrganizationService
	 */
	private $organizationService;
	/**
	 * @var NotificationService
	 */
	private $notificationService;
	private $listeners;

	public function __construct(OrganizationService $organizationService, NotificationService $notificationService){
		$this->organizationService = $organizationService;
		$this->notificationService = $notificationService;
	}

	public function attach(EventManagerInterface $events){

		$this->listeners[] = $events->getSharedManager()->attach(ImportDirector::class, ImportDirector::IMPORT_COMPLETED,
			function(Event $event) {
				$streamEvent = $event->getTarget();
				if(isset($streamEvent['importResult']) && isset($streamEvent['organizationId'])){
					$organization = $this->organizationService->findOrganization($streamEvent['organizationId']);
					$this->notificationService->sendKanbanizeImportResult($streamEvent['importResult'], $organization);
				}
		},200);
	}

	public function detach(EventManagerInterface $events)
	{
		foreach ($this->listeners as $index => $listener) {
			if($events->getSharedManager()->detach(ImportDirector::class, $listeners[$index])) {
				unset($this->listeners[$index]);
			}
		}
	}
}
<?php

namespace Kanbanize\Service;

use People\Service\OrganizationService;
use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\Event;
use Zend\EventManager\ListenerAggregateInterface;
use Prooph\EventStore\EventStore;
use Application\Service\UserService;
use People\Organization;

class ImportTasksListener implements ListenerAggregateInterface{
	
	/**
	 * @var OrganizationService
	 */
	private $organizationService;
	/**
	 * @var NotificationService
	 */
	private $notificationService;
	/**
	 * @var EventStore
	 */
	private $transactionManager;
	/**
	 * @var UserService
	 */
	protected $userService;
	private $listeners;

	public function __construct(OrganizationService $organizationService, NotificationService $notificationService, EventStore $transactionManager, UserService $userService){
		$this->organizationService = $organizationService;
		$this->notificationService = $notificationService;
		$this->transactionManager = $transactionManager;
		$this->userService = $userService;
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
		$this->listeners[] = $events->getSharedManager()->attach(ImportDirector::class, ImportDirector::CONNECTION_SUCCESS,
			function(Event $event) {
				$streamEvent = $event->getTarget();
				if(isset($streamEvent['apiKey']) && isset($streamEvent['organizationId'])
						&& isset($streamEvent['subdomain']) && isset($streamEvent['by'])){
					$organization = $this->organizationService->getOrganization($streamEvent['organizationId']);
					$updatedBy = $this->userService->findUser($streamEvent['by']);
					$this->transactionManager->beginTransaction();
					try{
						$kanbanizeSettings = [
							'accountSubdomain' => $streamEvent['subdomain'],
							'apiKey' => $streamEvent['apiKey']
						];
						$organization->setSetting(Organization::KANBANIZE_KEY_SETTING, $kanbanizeSettings, $updatedBy);
						$this->transactionManager->commit();
					}catch (\Exception $ex){
						$this->transactionManager->rollback();
						throw $e;
					}
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
<?php

namespace FlowManagement\Service;

use Zend\EventManager\Event;
use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\ListenerAggregateInterface;
use Zend\Mvc\Application;
use TaskManagement\TaskCreated;
use People\Service\OrganizationService;
use Application\Service\UserService;

class ItemCommandsListener implements ListenerAggregateInterface {
	
	protected $listeners = [];
	/**
	 * @var FlowService
	 */
	private $flowService;
	/**
	 * @var OrganizationService
	 */
	private $organizationService;
	/**
	 * @var UserService
	 */
	private $userService;
	
	public function __construct(FlowService $flowService, OrganizationService $organizationService, UserService $userService){
		$this->flowService = $flowService;
		$this->organizationService = $organizationService;
		$this->userService = $userService;
	}
	
	public function attach(EventManagerInterface $events) {
		$this->listeners[] = $events->getSharedManager()->attach(Application::class, TaskCreated::class, function(Event $event) {
			$streamEvent = $event->getTarget();
			$itemId = $streamEvent->metadata()['aggregate_id'];
			$organization = $this->organizationService->findOrganization($event->getParam('organizationId'));
			$orgMemberships = $this->organizationService->findOrganizationMemberships($organization, null, null);
			$createdBy = $this->userService->findUser($event->getParam('by'));
			$params = [$this->flowService, $itemId, $organization, $createdBy];
			array_walk($orgMemberships, function($member) use($params){
				$flowService = $params[0];
				$itemId = $params[1];
				$organization = $params[2];
				$createdBy = $params[3];
				$flowService->createVoteIdeaCard($member->getMember(), $itemId, $organization->getId(), $createdBy);
			});
		});
	}
	
	public function detach(EventManagerInterface $events){
		if($events->getSharedManager()->detach(Application::class, $this->listeners[0])) {
			unset($this->listeners[0]);
		}
	}
}
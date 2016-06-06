<?php

namespace FlowManagement\Service;

use Application\Service\UserService;
use People\Service\OrganizationService;
use Prooph\EventStore\EventStore;
use TaskManagement\TaskArchived;
use TaskManagement\TaskCreated;
use TaskManagement\TaskCompleted;
use TaskManagement\TaskOpened;
use TaskManagement\TaskAccepted;
use TaskManagement\TaskReopened;
use TaskManagement\TaskOngoing;
use TaskManagement\Task;
use TaskManagement\OwnerAdded;
use TaskManagement\TaskMemberRemoved;
use Zend\EventManager\Event;
use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\ListenerAggregateInterface;
use Zend\Mvc\Application;
use TaskManagement\Service\TaskService;
use People\Entity\OrganizationMembership;

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
	/**
	 * @var EventStore
	 */
	private $transactionManager;
	/**
	 * @var TaskService
	 */
	private $taskService;
	
	public function __construct(FlowService $flowService, 
			OrganizationService $organizationService, 
			UserService $userService, 
			EventStore $transactionManager,
			TaskService $taskService){
		$this->flowService = $flowService;
		$this->organizationService = $organizationService;
		$this->userService = $userService;
		$this->transactionManager = $transactionManager;
		$this->taskService = $taskService;
		$this->canVoteRoles = [OrganizationMembership::ROLE_ADMIN, OrganizationMembership::ROLE_MEMBER];
	}
	
	public function attach(EventManagerInterface $events) {
		$this->listeners[] = $events->getSharedManager()->attach(Application::class, TaskCreated::class, array($this, 'processItemCreated'));
		$this->listeners[] = $events->getSharedManager()->attach(Application::class, TaskArchived::class, array($this, 'processIdeaVotingClosed'));
		$this->listeners[] = $events->getSharedManager()->attach(Application::class, TaskOpened::class, array($this, 'processIdeaVotingClosed'));
		$this->listeners[] = $events->getSharedManager()->attach(Application::class, TaskOngoing::class, array($this, 'processItemOngoing'));
		$this->listeners[] = $events->getSharedManager()->attach(Application::class, TaskCompleted::class, array($this, 'processItemCompleted'));
		$this->listeners[] = $events->getSharedManager()->attach(Application::class, TaskAccepted::class, array($this, 'processItemCompletedVotingClosed'));
		$this->listeners[] = $events->getSharedManager()->attach(Application::class, TaskReopened::class, array($this, 'processItemCompletedReopened'));
		$this->listeners [] = $events->getSharedManager()->attach(Application::class, OwnerAdded::class, array($this, 'processItemOwnerChanged'));
		$this->listeners [] = $events->getSharedManager()->attach(Application::class, TaskMemberRemoved::class, array($this, 'processItemMemberRemoved'));
	}
	
	public function processItemCreated(Event $event){
		$streamEvent = $event->getTarget();
		$itemId = $streamEvent->metadata()['aggregate_id'];
		$organization = $this->organizationService->findOrganization($event->getParam('organizationId'));
		$orgMemberships = $this->organizationService->findOrganizationMemberships($organization, null, null, $this->canVoteRoles);
		$createdBy = $this->userService->findUser($event->getParam('by'));
		$params = [$this->flowService, $itemId, $organization, $createdBy];
		array_walk($orgMemberships, function($member) use($params){
			$flowService = $params[0];
			$itemId = $params[1];
			$organization = $params[2];
			$createdBy = $params[3];
			$flowService->createVoteIdeaCard($member->getMember(), $itemId, $organization->getId(), $createdBy);
		});
	}
	
	public function processItemOngoing(Event $event){
		$streamEvent = $event->getTarget();
		$itemId = $streamEvent->metadata()['aggregate_id'];
		$item = $this->taskService->getTask($itemId);		

		$changedBy = $this->userService->findUser($event->getParam('by'));

		$this->transactionManager->beginTransaction();
		try {
			$item->changeOwner($changedBy, $changedBy);
			$this->transactionManager->commit();
		}catch( \Exception $e ) {
			$this->transactionManager->rollback();
			throw $e;
		}
	}
	
	public function processItemCompleted(Event $event){
		$streamEvent = $event->getTarget();
		$itemId = $streamEvent->metadata()['aggregate_id'];
		$organization = $this->organizationService->findOrganization($event->getParam('organizationId'));
		$orgMemberships = $this->organizationService->findOrganizationMemberships($organization, null, null, $this->canVoteRoles);
		$createdBy = $this->userService->findUser($event->getParam('by'));
		$params = [$this->flowService, $itemId, $organization, $createdBy];
		array_walk($orgMemberships, function($member) use($params){
			$flowService = $params[0];
			$itemId = $params[1];
			$organization = $params[2];
			$completedBy = $params[3];
			$flowService->createVoteCompletedItemCard($member->getMember(), $itemId, $organization->getId(), $completedBy);
		});
	}	
	
	public function processIdeaVotingClosed(Event $event) {
		$streamEvent = $event->getTarget();
		$item = $this->taskService->findTask($streamEvent->metadata()['aggregate_id']);
		//recupero le card del flow che sono associate a questo item
		$flowCards = $this->flowService->findFlowCardsByItem($item);
		$params = [$this->flowService, $this->transactionManager];
		array_walk($flowCards, function($card) use($params){
			$flowService = $params[0];
			$transactionManager = $params[1];
			$wmCard = $flowService->getCard($card->getId());
			$transactionManager->beginTransaction();
			try {
				$wmCard->hide();
				$transactionManager->commit();
			}catch( \Exception $e ) {
				$transactionManager->rollback();
				throw $e;
			}
		});
	}

	public function processItemCompletedVotingClosed(Event $event){
		$streamEvent = $event->getTarget();
		$itemId = $streamEvent->metadata()['aggregate_id'];

		$item = $this->taskService->findTask($itemId);

		//recupero le card del flow che sono associate a questo item
		$flowCards = $this->flowService->findFlowCardsByItem($item);

		// chiusura delle precedenti card aperte per questo item
		$params = [$this->flowService, $this->transactionManager];
		array_walk($flowCards, function($card) use($params){
			$flowService = $params[0];
			$transactionManager = $params[1];
			$wmCard = $flowService->getCard($card->getId());
			$transactionManager->beginTransaction();
			try {
				$wmCard->hide();
				$transactionManager->commit();
			}catch( \Exception $e ) {
				$transactionManager->rollback();
				throw $e;
			}
		});

		// creazione di nuove card per notificare la chiusura del processo di voto
		$organization = $this->organizationService->findOrganization($event->getParam('organizationId'));
		$orgMemberships = $this->organizationService->findOrganizationMemberships($organization, null, null, $this->canVoteRoles);
		$createdBy = $this->userService->findUser($event->getParam('by'));
		$params = [$this->flowService, $itemId, $organization, $createdBy];
		array_walk($orgMemberships, function($member) use($params){
			$flowService = $params[0];
			$itemId = $params[1];
			$organization = $params[2];
			$completedBy = $params[3];
			$flowService->createVoteCompletedItemVotingClosedCard($member->getMember(), $itemId, $organization->getId(), $completedBy);
		});
	}

	public function processItemCompletedReopened(Event $event){
		$streamEvent = $event->getTarget();
		$itemId = $streamEvent->metadata()['aggregate_id'];
		$item = $this->taskService->findTask($itemId);

		//recupero le card del flow che sono associate a questo item
		$flowCards = $this->flowService->findFlowCardsByItem($item);

		// chiusura delle precedenti card aperte per questo item
		$params = [$this->flowService, $this->transactionManager];
		array_walk($flowCards, function($card) use($params){
			$flowService = $params[0];
			$transactionManager = $params[1];
			$wmCard = $flowService->getCard($card->getId());
			$transactionManager->beginTransaction();
			try {
				$wmCard->hide();
				$transactionManager->commit();
			}catch( \Exception $e ) {
				$transactionManager->rollback();
				throw $e;
			}
		});

		// creazione di nuove card per notificare la chiusura del processo di voto
		$organization = $this->organizationService->findOrganization($event->getParam('organizationId'));
		$orgMemberships = $this->organizationService->findOrganizationMemberships($organization, null, null, $this->canVoteRoles);
		$reopenedBy = $this->userService->findUser($event->getParam('by'));
		$params = [$this->flowService, $itemId, $organization, $reopenedBy];
		array_walk($orgMemberships, function($member) use($params){
			$flowService = $params[0];
			$itemId = $params[1];
			$organization = $params[2];
			$completedBy = $params[3];
			$flowService->createVoteCompletedItemReopenedCard($member->getMember(), $itemId, $organization->getId(), $completedBy);
		});		
	}

	public function processItemOwnerChanged(Event $event) {
		if (is_null($event->getParam('ex_owner'))) {
			return;
		}

		$organization = $this->organizationService->findOrganization($event->getParam('organizationId'));

		$streamEvent = $event->getTarget();
		$itemId = $streamEvent->metadata()['aggregate_id'];

		$exOwner = $this->userService->findUser($event->getParam('ex_owner'));
		$changedBy = $this->userService->findUser($event->getParam('by'));

		$this->flowService->createItemOwnerChangedCard($exOwner, $itemId, $organization->getId(), $changedBy);		
	}

	public function processItemMemberRemoved(Event $event) {
		if (is_null($event->getParam('ex_member'))) {
			return;
		}

		$organization = $this->organizationService->findOrganization($event->getParam('organizationId'));

		$streamEvent = $event->getTarget();
		$itemId = $streamEvent->metadata()['aggregate_id'];

		$exMember = $this->userService->findUser($event->getParam('ex_member'));
		$changedBy = $this->userService->findUser($event->getParam('by'));

		$this->flowService->createItemMemberRemovedCard($exMember, $itemId, $organization->getId(), $changedBy);		
	}
	
	public function detach(EventManagerInterface $events){
		foreach ( $this->listeners as $index => $listener ) {
			if ($events->detach ( $listener )) {
				unset ( $this->listeners [$index] );
			}
		}
	}
}
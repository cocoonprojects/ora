<?php

namespace Kanbanize\Service;

use Application\Entity\User;
use Application\Service\UserService;
use Kanbanize\KanbanizeStream;
use Kanbanize\KanbanizeTask;
use People\Organization;
use Prooph\EventStore\EventStore;
use Rhumsaa\Uuid\Uuid;
use TaskManagement\Service\StreamService;
use TaskManagement\Service\TaskService;
use TaskManagement\Entity\Task as ReadModelTask;
use TaskManagement\Entity\TaskMember;
use Zend\EventManager\EventManagerAwareInterface;
use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\EventManager;


class ImportDirector implements EventManagerAwareInterface{
	
	CONST IMPORT_COMPLETED = "Import.Completed";

	/**
	 * @var KanbanizeService
	 */
	protected $kanbanizeService;
	/**
	 * @var TaskService
	 */
	protected $taskService;
	/**
	 * @var StreamService
	 */
	protected $streamService;
	/**
	 * @var EventStore
	 */
	protected $transactionManager;
	/**
	 * @var UserService
	 */
	protected $userService;
	
	private $apiKey;
	/**
	 *
	 * @var EventManagerInterface
	 */
	protected $events;

	public function __construct(KanbanizeService $kanbanizeService,
			TaskService $taskService,
			StreamService $streamService,
			EventStore $transactionManager,
			UserService $userService){
		$this->kanbanizeService = $kanbanizeService;
		$this->taskService = $taskService;
		$this->streamService = $streamService;
		$this->transactionManager = $transactionManager;
		$this->userService = $userService;
	}
	/**
	 * 
	 * @param Organization $organization
	 * @param User $requestedBy
	 */
	public function import(Organization $organization, User $requestedBy){
		$api = $this->initApi($organization);
		$importer = new Importer(
			$this->kanbanizeService,
			$this->taskService,
			$this->streamService,
			$this->transactionManager,
			$this->userService,
			$organization,
			$requestedBy,
			$api
		);
		$importer->importProjects();
		$importResult = $importer->getImportResult();
		$this->getEventManager()->trigger(self::IMPORT_COMPLETED, [
			'importResult' => $importResult,
			'organizationId' => $organization->getId()
		]);
		return $importResult;
	}

	public function setApiKey($key){
		$this->apiKey = $key;
		return $this;
	}
	
	private function initApi(Organization $organization){
		$api = new KanbanizeAPI();
		$api->setApiKey($this->apiKey);
		$api->setUrl($organization->getSetting("kanbanizeAccountAddress"));
		return $api;
	}
	
	public function setEventManager(EventManagerInterface $events){
		$events->setIdentifiers(array(
				'Kanbanize\ImportDirector',
				__CLASS__,
				get_class($this)
		));
		$this->events = $events;
	}
	
	public function  getEventManager(){
		if (!$this->events) {
			$this->setEventManager(new EventManager());
		}
		return $this->events;
	}
}
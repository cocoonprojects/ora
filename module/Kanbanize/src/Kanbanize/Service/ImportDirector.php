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
use People\Service\OrganizationService;



class ImportDirector {
	
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
	/**
	 * @var OrganizationService
	 */
	protected $organizationService;
	
	private $apiKey;

	public function __construct(KanbanizeService $kanbanizeService,
			TaskService $taskService,
			StreamService $streamService,
			EventStore $transactionManager,
			UserService $userService,
			OrganizationService $organizationService){
		$this->kanbanizeService = $kanbanizeService;
		$this->taskService = $taskService;
		$this->streamService = $streamService;
		$this->transactionManager = $transactionManager;
		$this->userService = $userService;
		$this->organizationService = $organizationService;
	}
	/**
	 * 
	 * @param Organization $organization
	 * @param User $requestedBy
	 */
	public function import(Organization $organization, User $requestedBy){
		$api = $this->initApi($organization, $this->apiKey);
		$importer = new Importer(
				$this->kanbanizeService,
				$this->taskService,
				$this->streamService,
				$this->transactionManager,
				$this->userService,
				$organization,
				$requestedBy,
				$api,
				$this->organizationService
		);
		$importer->importProjects();
		return $importer->getImportResult();
	}

	public function setApiKey($key){
		$this->apiKey = $key;
		return $this;
	}
	
	private function initApi(Organization $organization, $apiKey){
		$api = new KanbanizeAPI();
		$api->setApiKey($this->apiKey);
		$api->setUrl($organization->getSetting("url"));
		return $api;
	}
}
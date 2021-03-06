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
	
	CONST IMPORT_COMPLETED = "KanbanizeImport.Completed";
	CONST API_URL_FORMAT = "https://%s.kanbanize.com/index.php/api/kanbanize";
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
	 *
	 * @var EventManagerInterface
	 */
	protected $events;
	/**
	 * @var \DateInterval
	 */
	protected $intervalForAssignShares;

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
		$this->intervalForAssignShares = new \DateInterval('P7D');
	}
	/**
	 * 
	 * @param Organization $organization
	 * @param User $requestedBy
	 */
	public function import(Organization $organization, User $requestedBy){
		try{
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
			$importer->setIntervalForAssignShares($this->getIntervalForAssignShares());
			$importer->importProjects();
			$importResult = $importer->getImportResult();
			$this->getEventManager()->trigger(self::IMPORT_COMPLETED, [
				'importResult' => $importResult,
				'organizationId' => $organization->getId()
			]);
			return $importResult; 
		}catch (\Exception $e){
			return ['errors'=>[$e->getMessage()]];
		}
	}

	private function initApi(Organization $organization){
		$api = new KanbanizeAPI();
		$apiKey = $organization->getSetting("kanbanizeApiKey");
		if(empty($apiKey)){
			throw new \Exception("Cannot import projects due to missing api key");
		}
		$api->setApiKey($apiKey);
		$api->setUrl(sprintf(self::API_URL_FORMAT, $organization->getSetting("kanbanizeAccountSubdomain")));
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

	public function setIntervalForAssignShares(\DateInterval $interval){
		$this->intervalForAssignShares = $interval;
	}

	public function getIntervalForAssignShares(){
		return $this->intervalForAssignShares;
	}
}
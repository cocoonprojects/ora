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
	CONST CONNECTION_SUCCESS = "KanbanizeConnection.Success";
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
			$projects = $importer->importProjects();
			foreach ($projects as $project){
				$importer->importProject($project);
			}
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

	private function initApi($apiKey, $subdomain){
		$api = new KanbanizeAPI();
		if(is_null($apiKey)){
			throw new \Exception("Cannot import projects due to missing api key");
		}
		if(is_null($subdomain)){
			throw new \Exception("Cannot import projects due to missing account subdomain");
		}
		$api->setApiKey($apiKey);
		$api->setUrl(sprintf(self::API_URL_FORMAT, $subdomain));
		return $api;
	}

	public function testConnectionSettings(Organization $organization, User $requestedBy, $apiKey, $subdomain){
		try{
			$api = $this->initApi($apiKey, $subdomain);
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
			$projects = $importer->importProjects();
			$this->getEventManager()->trigger(self::CONNECTION_SUCCESS, [
				'apiKey' => $apiKey,
				'subdomain' => $subdomain,
				'organizationId' => $organization->getId(),
				'by' => $requestedBy->getId()
			]);
			return [
				'projects' => $projects
			];
		}catch (\Exception $e){
			return ['errors'=>[$e->getMessage()]];
		}
	}

	public function importBoardColumns(Organization $organization, User $requestedBy, $boardId){
		$kanbanizeSettings = $organization->getSetting(Organization::KANBANIZE_KEY_SETTING);
		try{
			$api = $this->initApi($kanbanizeSettings['apiKey'], $kanbanizeSettings['accountSubdomain']);
			$structure = $api->getBoardStructure($boardId);
			if(is_string($structure)){
				return ['errors' => ["Cannot import columns for boardId: {$boardId}, due to: {$structure}"]];
			}
			$mappedColumns = [];
			foreach($structure['columns'] as $column){
				$mappedColumns[$column['lcname']] = null;
			}
			if(isset($kanbanizeSettings['boards'][$boardId]['columnMapping'])){
				$mergedMapping = array_merge($mappedColumns, $kanbanizeSettings[$boardId]['columnMapping']);
				$columnsToDelete = array_diff_key($mergedMapping, $mappedColumns);
				foreach($columnsToDelete as $key=>$value){
					unset($mergedMapping[$key]);
				}
				$mappedColumns = $mergedMapping;
			}
			return [
				'columns' => $mappedColumns,
				'errors' => []
			];
		}catch(KanbanizeApiException $e){
			return ['errors' => ["Cannot import columns due to {$e->getMessage()}"]];
		}
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

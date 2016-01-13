<?php 

namespace Kanbanize\Controller;

use Application\Controller\OrganizationAwareController;
use Application\Service\UserService;
use Kanbanize\Service\KanbanizeAPI;
use Kanbanize\Service\Importer;
use Kanbanize\Service\KanbanizeService;
use People\Organization;
use People\Service\OrganizationService;
use TaskManagement\Service\StreamService;
use TaskManagement\Service\TaskService;
use Zend\View\Model\JsonModel;
use Application\View\ErrorJsonModel;

class ImportsController extends OrganizationAwareController{

	protected static $collectionOptions = ['POST'];
	protected static $resourceOptions = [];
	CONST IMPORT_COMPLETED = "KanbanizeImport.Completed";

	/**
	 * @var KanbanizeService
	 */
	private $kanbanizeService;

	/**
	 * @var KanbanizeAPI
	 */
	private $client;
	/**
	 * @var \DateInterval
	 */
	private $intervalForAssignShares;
	/**
	 * @var TaskService
	 */
	private $taskService;
	/**
	 * @var UserService
	 */
	private $userService;
	/**
	 * @var StreamService
	 */
	private $streamService;

	public function __construct(OrganizationService $orgService, 
			KanbanizeAPI $client, 
			KanbanizeService $kanbanizeService, 
			TaskService $taskService, 
			UserService $userService,
			StreamService $streamService){
		parent::__construct($orgService);
		$this->client = $client;
		$this->intervalForAssignShares = new \DateInterval('P7D');
		$this->kanbanizeService = $kanbanizeService;
		$this->taskService = $taskService;
		$this->userService = $userService;
		$this->streamService = $streamService;
	}

	public function create($data){

		if(is_null($this->identity())) {
			$this->response->setStatusCode(401);
			return $this->response;
		}

		if(!$this->isAllowed($this->identity(), $this->organization, 'Kanbanize.Task.import')) {
			$this->response->setStatusCode(403);
			return $this->response;
		}
		$organization = $this->getOrganizationService()->getOrganization($this->organization->getId());
		$kanbanizeSettings = $organization->getSettings(Organization::KANBANIZE_SETTINGS);
		try{
			$this->initApi($kanbanizeSettings['apiKey'], $kanbanizeSettings['accountSubdomain']);
			$importer = new Importer(
					$this->kanbanizeService,
					$this->taskService,
					$this->transaction(),
					$this->userService,
					$organization,
					$this->identity(),
					$this->client
			);
			$importer->setIntervalForAssignShares($this->intervalForAssignShares);
			foreach(array_keys($kanbanizeSettings['boards']) as $boardId){
				$kanbanizeStream = $this->kanbanizeService->findStreamByBoardId($boardId, $organization);
				//TODO: esplorare nuovi metadati per l'event store 
				//in modo da poter ricercare uno stream anche in base al contenuto del payload (es: $boardId)
				$stream = $this->streamService->getStream($kanbanizeStream->getId());
				$importer->importTasks($boardId, $stream);
			}
			$importResult = $importer->getImportResult();
			$this->getEventManager()->trigger(self::IMPORT_COMPLETED, [
				'importResult' => $importResult,
				'organizationId' => $organization->getId()
			]);
			$this->response->setStatusCode(200);
			return new JsonModel($importResult);
		}catch(\Exception $ex){
			$error = new ErrorJsonModel();
			$error->setCode(400);
			$error->setDescription("Cannot import tasks due to: {$ex->getMessage()}");
			$this->response->setStatusCode(400);
			return $error;
		}
	}

	public function getKanbanizeImporter(){
		return $this->kanbanizeImporter;
	}

	public function getNotificationService(){
		return $this->notificationService;
	}
	protected function getCollectionOptions() {
		return self::$collectionOptions;
	}

	protected function getResourceOptions() {
		return self::$resourceOptions;
	}
	private function initApi($apiKey, $subdomain){
		if(is_null($apiKey)){
			throw new \Exception("Cannot connect to Kanbanize due to missing api key");
		}
		if(is_null($subdomain)){
			throw new \Exception("Cannot connect to Kanbanize due to missing account subdomain");
		}
		$this->client->setApiKey($apiKey);
		$this->client->setUrl(sprintf(Importer::API_URL_FORMAT, $subdomain));
	}
	public function setIntervalForAssignShares(\DateInterval $interval){
		$this->intervalForAssignShares = $interval;
	}
	
	public function getIntervalForAssignShares(){
		return $this->intervalForAssignShares;
	}
	public function getStreamService(){
		return $this->streamService;
	}
	public function getKanbanizeService(){
		return $this->kanbanizeService;
	}
	public function getKanbanizeClient(){
		return $this->client;
	}
	public function getTaskService(){
		return $this->taskService;
	}
}
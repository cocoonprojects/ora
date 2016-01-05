<?php

namespace Kanbanize\Controller;

use Application\Controller\OrganizationAwareController;
use Application\View\ErrorJsonModel;
use People\Service\OrganizationService;
use People\Organization;
use Kanbanize\Service\KanbanizeAPI;
use Kanbanize\Service\KanbanizeApiException;
use Kanbanize\Service\Importer;
use TaskManagement\Service\StreamService;
use TaskManagement\Task;
use Zend\Filter\FilterChain;
use Zend\Filter\StringTrim;
use Zend\Filter\StripNewlines;
use Zend\Filter\StripTags;
use Zend\View\Model\JsonModel;
use Zend\Validator\InArray;
use Zend\Validator\NotEmpty;
use Kanbanize\KanbanizeStream;


class BoardsController extends OrganizationAwareController{

	protected static $resourceOptions = ['POST', 'GET'];
	protected static $collectionOptions= [];
	/**
	 * @var StreamService
	 */
	private $streamService;
	/**
	 * @var KanbanizeAPI
	 */
	private $client;

	public function __construct(OrganizationService $organizationService, StreamService $streamService, KanbanizeAPI $client){
		parent::__construct($organizationService);
		$this->streamService = $streamService;
		$this->client = $client;
	}

	public function invoke($id, $data){
		if(is_null($this->identity())) {
			$this->response->setStatusCode(401);
			return $this->response;
		}
		if(!$this->isAllowed($this->identity(), $this->organization, 'Kanbanize.BoardSettings.create')) {
			$this->response->setStatusCode(403);
			return $this->response;
		}
		$streamValidator = new NotEmpty();
		$error = new ErrorJsonModel();
		if(!(isset($data['projectId']) && $streamValidator->isValid($data['projectId']))){
			$error->addSecondaryErrors("projectId", ["Missing project id"]);
		}
		if(!(isset($data['streamName']) && $streamValidator->isValid($data['streamName']))){
			$error->addSecondaryErrors("streamName", ["Stream name cannot be empty"]);
		}
		if($error->hasErrors()){
			$error->setCode(400);
			$error->setDescription('Some parameters are not valid');
			$this->response->setStatusCode(400);
			return $error;
		}
		$filters = new FilterChain();
		$filters->attach(new StringTrim())
			->attach(new StripNewlines())
			->attach(new StripTags());
		$streamName = $filters->filter($data['streamName']);
		$projectId = $data['projectId'];
		unset($data['streamName']);
		unset($data['projectId']);

		$statusValidator = new InArray([
			'haystack' => [
					Task::STATUS_IDEA,
					Task::STATUS_OPEN,
					Task::STATUS_ONGOING,
					Task::STATUS_COMPLETED,
					Task::STATUS_ACCEPTED,
					Task::STATUS_CLOSED
			]
		]);
		$columnMapping = [];
		foreach($data as $column=>$status){
			if(!$statusValidator->isValid($status)){
				$error->addSecondaryErrors($column, ["Invalid status: {$status}"]);
			}
		}
		if($error->hasErrors()){
			$error->setCode(400);
			$error->setDescription('Some parameters are not valid');
			$this->response->setStatusCode(400);
			return $error;
		}

		$organization = $this->getOrganizationService()->getOrganization($this->organization->getId());
		$kanbanizeSettings = $organization->getSetting(Organization::KANBANIZE_KEY_SETTING);
		$kanbanizeSettings['boards'][$id]['columnMapping'] = $columnMapping;
		$this->transaction()->begin();
		try{
			$stream = $this->createStream($streamName, $projectId, $id, $organization);
			$organization->setSetting(Organization::KANBANIZE_KEY_SETTING, $kanbanizeSettings, $this->identity());
			$this->transaction()->commit();
			$this->response->setStatusCode(201);
			return new JsonModel([
				'streamName' => $stream->getSubject(),
				'boardId' => $id,
				'boardSettings' => $organization->getSetting(Organization::KANBANIZE_KEY_SETTING)['boards'][$id]
			]);
		}catch (\Exception $e) {
			$this->transaction()->rollback();
			$this->response->setStatusCode(500);
			return $this->response;
		}
	}
	
	public function get($id){
		if(is_null($this->identity())) {
			$this->response->setStatusCode(401);
			return $this->response;
		}
		if(!$this->isAllowed($this->identity(), $this->organization, 'Kanbanize.BoardSettings.get')) {
			$this->response->setStatusCode(403);
			return $this->response;
		}
		$organization = $this->getOrganizationService()->getOrganization($this->organization->getId());
		$kanbanizeSettings = $organization->getSetting(Organization::KANBANIZE_KEY_SETTING);
		try{
			$this->initApi($kanbanizeSettings['apiKey'], $kanbanizeSettings['accountSubdomain']);
			$structure = $this->client->getBoardStructure($id);
			if(is_string($structure)){
				//TODO: il metodo getProjectsAndBoards, se va a buon fine, restituisce un array; in caso di errore non restituisce un messaggio completo ma solamente il primo carattere
				//migliorare questo comportamento
				$error = new ErrorJsonModel();
				$error->setCode(400);
				$error->setDescription("Cannot import structure for boardId: {$id}, due to: {$structure}");
				$this->response->setStatusCode(400);
				return $error;
			}
			$mappedColumns = [];
			foreach($structure['columns'] as $column){
				$mappedColumns[$column['lcname']] = "";
			}
			if(isset($kanbanizeSettings['boards'][$id]['columnMapping'])){
				$mergedMapping = array_merge($mappedColumns, $kanbanizeSettings['boards'][$id]['columnMapping']);
				$columnsToDelete = array_diff_key($mergedMapping, $mappedColumns);
				foreach($columnsToDelete as $key=>$value){
					unset($mergedMapping[$key]);
				}
				$mappedColumns = $mergedMapping;
			}
			return new JsonModel([
				'boardId' => $id,
				'mapping' => $mappedColumns
			]);
		}catch (KanbanizeApiException $e){
			$this->errors[] = "Cannot import structure for boardId: {$id}, due to: {$e->getMessage()}";
		}
	}
	protected function getCollectionOptions() {
		return self::$collectionOptions;
	}
	protected function getResourceOptions() {
		return self::$resourceOptions;
	}
	private function initApi($apiKey, $subdomain){
		if(is_null($apiKey)){
			throw new KanbanizeApiException("Cannot connect to Kanbanize due to missing api key");
		}
		if(is_null($subdomain)){
			throw new KanbanizeApiException("Cannot connect to Kanbanize due to missing account subdomain");
		}
		$this->client->setApiKey($apiKey);
		$this->client->setUrl(sprintf(Importer::API_URL_FORMAT, $subdomain));
	}
	private function createStream($subject, $projectId, $boardId, Organization $organization) {
		$options = [
				'projectId' => $projectId,
				'boardId' => $boardId
		];
		$stream = KanbanizeStream::create($organization, $subject, $this->identity(), $options);
		$this->streamService->addStream($stream);
		return $stream;
	}
}